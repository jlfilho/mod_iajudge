<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Ad-hoc task that sends a student submission to the AI provider for evaluation.
 *
 * This task is dispatched by view.php immediately after a student submits code.
 * It runs in the background when the Moodle cron processes the task queue.
 *
 * Execution flow:
 *   1. Read submission + activity instance from DB.
 *   2. Mark submission as 'processing'.
 *   3. Build the evaluation prompt (rubric + code).
 *   4. Call the configured AI provider.
 *   5. Parse the JSON response (score + feedback).
 *   6. Persist the grade in iajudge_grade.
 *   7. Update submission status to 'graded'.
 *   8. Push grade to the Moodle Gradebook.
 *
 * On any failure, the submission is marked as 'error' and the exception is
 * re-thrown so the cron runner can log it and retry according to its policy.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_iajudge\task;

use mod_iajudge\ai\provider_factory;

/**
 * Background task: sends student code to the AI and records the result.
 */
class grade_submission extends \core\task\adhoc_task {

    /**
     * Returns a human-readable name for this task (shown in the admin task log).
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('pluginname', 'mod_iajudge') . ': Grade Submission';
    }

    /**
     * Executes the task.
     *
     * @throws \moodle_exception Propagated on unrecoverable errors so cron can log them.
     */
    public function execute(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/iajudge/lib.php');
        require_once($CFG->dirroot . '/mod/iajudge/locallib.php');

        // ---------------------------------------------------------------
        // 1. Retrieve the submission ID from the task payload.
        // ---------------------------------------------------------------
        $data = $this->get_custom_data();

        if (empty($data->submissionid)) {
            mtrace('mod_iajudge grade_submission: Missing submissionid in task payload.');
            return;
        }

        $submissionid = (int) $data->submissionid;

        // ---------------------------------------------------------------
        // 2. Load the submission record.
        // ---------------------------------------------------------------
        $submission = $DB->get_record('iajudge_submission', ['id' => $submissionid]);

        if (!$submission) {
            mtrace("mod_iajudge grade_submission: Submission {$submissionid} not found — skipping.");
            return;
        }

        // Defensive check: skip if already graded (e.g. duplicate task dispatch).
        if ($submission->status === 'graded') {
            mtrace("mod_iajudge grade_submission: Submission {$submissionid} already graded — skipping.");
            return;
        }

        // ---------------------------------------------------------------
        // 3. Load the parent iajudge activity instance.
        // ---------------------------------------------------------------
        $iajudge = $DB->get_record('iajudge', ['id' => $submission->iajudgeid], '*', MUST_EXIST);

        // ---------------------------------------------------------------
        // 4. Mark submission as 'processing' to prevent duplicate evaluation.
        // ---------------------------------------------------------------
        $DB->set_field('iajudge_submission', 'status', 'processing', ['id' => $submissionid]);

        try {
            // -----------------------------------------------------------
            // 5. Build the evaluation prompt.
            // -----------------------------------------------------------
            $prompt = $this->build_prompt(
                $iajudge->rubric_prompt,
                $submission->language,
                $submission->code
            );

            mtrace("mod_iajudge grade_submission: Evaluating submission {$submissionid} "
                 . "({$submission->language}) via AI provider…");

            // -----------------------------------------------------------
            // 6. Get the configured AI provider and call evaluate().
            // -----------------------------------------------------------
            $provider = provider_factory::get_provider();
            $result   = $provider->evaluate($prompt);

            // $result = ['score' => float, 'feedback' => string, 'raw' => string]
            $score    = (float)  $result['score'];
            $feedback = (string) $result['feedback'];
            $raw      = (string) $result['raw'];

            // Clamp score to 0–100 (defensive).
            $score = max(0.0, min(100.0, $score));

            // -----------------------------------------------------------
            // 7. Persist the grade.
            // -----------------------------------------------------------
            // Check if a grade record already exists (avoid duplicates).
            $existing = $DB->get_record('iajudge_grade', ['submissionid' => $submissionid]);

            if ($existing) {
                $existing->score        = $score;
                $existing->feedback     = $feedback;
                $existing->raw_response = $raw;
                $existing->timecreated  = time();
                $DB->update_record('iajudge_grade', $existing);
            } else {
                $grade                = new \stdClass();
                $grade->submissionid  = $submissionid;
                $grade->score         = $score;
                $grade->feedback      = $feedback;
                $grade->raw_response  = $raw;
                $grade->timecreated   = time();
                $DB->insert_record('iajudge_grade', $grade);
            }

            // -----------------------------------------------------------
            // 8. Update submission status to 'graded'.
            // -----------------------------------------------------------
            $DB->set_field('iajudge_submission', 'status', 'graded', ['id' => $submissionid]);

            // -----------------------------------------------------------
            // 9. Push grade to the Moodle Gradebook.
            // -----------------------------------------------------------
            iajudge_update_grades($iajudge, $submission->userid);

            mtrace("mod_iajudge grade_submission: Submission {$submissionid} graded — score: {$score}.");

        } catch (\Throwable $e) {
            // Mark as error so the student sees the failure state in the UI.
            $DB->set_field('iajudge_submission', 'status', 'error', ['id' => $submissionid]);

            mtrace("mod_iajudge grade_submission: ERROR for submission {$submissionid}: " . $e->getMessage());

            // Re-throw as a moodle_exception so cron logs the full stack trace.
            throw new \moodle_exception(
                'error_ai_response_invalid',
                'mod_iajudge',
                '',
                null,
                $e->getMessage()
            );
        }
    }

    /**
     * Builds the structured prompt sent to the AI provider.
     *
     * The prompt is designed to produce a strict JSON response:
     *   {"score": <0-100>, "feedback": "<text>"}
     *
     * @param  string $rubric   The teacher's correction rubric.
     * @param  string $language The programming language (python|c|java|javascript).
     * @param  string $code     The student's source code.
     * @return string           The full prompt string.
     */
    private function build_prompt(string $rubric, string $language, string $code): string {
        // Map internal keys to display names.
        $langnames = [
            'python'     => 'Python',
            'c'          => 'C',
            'java'       => 'Java',
            'javascript' => 'JavaScript',
        ];
        $langdisplay = $langnames[$language] ?? strtoupper($language);

        return <<<PROMPT
You are a pedagogical code evaluator. Evaluate the student's code according to the rubric below.

Return ONLY a valid JSON object in this exact format (no markdown fences, no extra text):
{"score": <number between 0 and 100>, "feedback": "<your pedagogical feedback here>"}

RUBRIC:
{$rubric}

PROGRAMMING LANGUAGE: {$langdisplay}

STUDENT CODE:
```{$language}
{$code}
```

Remember:
- Be constructive and pedagogical in your feedback.
- Do NOT give away the complete solution.
- Point out specific lines or patterns that need improvement.
- Respond ONLY with the JSON object.
PROMPT;
    }
}
