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
 * Helper for assembling AI grading jobs.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_codejudge\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Shared helpers used by the grading queue and future request entry points.
 */
class grading_helper {

    /**
     * Normalises source code before it is stored or sent to the AI provider.
     *
     * @param string $code Source code.
     * @return string
     */
    public static function normalise_code(string $code): string {
        return str_replace(["\r\n", "\r"], "\n", $code);
    }

    /**
     * Converts rich question text into plain text for the AI prompt.
     *
     * @param string $text Question text.
     * @return string
     */
    public static function plain_text(string $text): string {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        return trim(strip_tags($text));
    }

    /**
     * Builds the prompt sent to the AI provider.
     *
     * @param string $questiontext Question statement.
     * @param string $rubric Rubric or grading instructions.
     * @param string $language Programming language key.
     * @param string $code Student code.
     * @param string $startercode Optional starter code.
     * @param string $feedbacklanguage Moodle user language for the correction comment.
     * @return string
     */
    public static function build_prompt(
        string $questiontext,
        string $rubric,
        string $language,
        string $code,
        string $startercode = '',
        string $feedbacklanguage = ''
    ): string {
        $language = language_helper::normalise($language);
        $languages = language_helper::get_options();
        $languagelabel = $languages[$language] ?? $language;
        $feedbacklanguage = self::normalise_feedback_language($feedbacklanguage);
        $questiontext = self::plain_text($questiontext);
        $rubric = trim($rubric);
        $code = self::normalise_code(trim($code));
        $startercode = self::normalise_code(trim($startercode));

        $prompt = [];
        $prompt[] = 'You are an expert pedagogical code evaluator.';
        $prompt[] = 'Return ONLY a valid JSON object with this exact structure:';
        $prompt[] = '{"score": <number between 0 and 100>, "feedback": "<pedagogical feedback>"}';
        $prompt[] = 'Write the feedback value in this Moodle user language: ' . $feedbacklanguage . '.';
        $prompt[] = 'Do not switch languages unless code identifiers or expected output literals require it.';
        $prompt[] = '';

        if ($questiontext !== '') {
            $prompt[] = 'QUESTION:';
            $prompt[] = $questiontext;
            $prompt[] = '';
        }

        if ($rubric !== '') {
            $prompt[] = 'RUBRIC:';
            $prompt[] = $rubric;
            $prompt[] = '';
        }

        $prompt[] = 'PROGRAMMING LANGUAGE: ' . $languagelabel;
        $prompt[] = '';
        $prompt[] = 'LANGUAGE-COMPLIANCE RULE:';
        $prompt[] = 'The expected language for this answer is: ' . $languagelabel . '.';
        $prompt[] = 'Grade language compliance before algorithm correctness.';
        $prompt[] = 'If the submission is primarily written in a different programming language, it must not receive a high score, even if the algorithm is correct.';
        $prompt[] = 'If the algorithm is correct but the language is wrong, the score must be at most 30.';
        $prompt[] = 'If the answer cannot reasonably be interpreted as the expected language, the score must be at most 20.';
        $prompt[] = 'Mention in the feedback when the solution does not follow the required language.';
        $prompt[] = '';

        if ($language === 'portugol') {
            $prompt[] = 'PORTUGOL-SPECIFIC GRADING RULES:';
            $prompt[] = 'Language compliance is mandatory and must be graded before algorithm correctness.';
            $prompt[] = 'A submission written primarily in Python, C, Java, JavaScript, or another executable programming language is not a valid Portugol answer, even if the algorithm is correct.';
            $prompt[] = 'If the answer is not written as Portugol or formal structured pseudocode, the score must be at most 30.';
            $prompt[] = 'If the answer is executable code in another programming language, the score must be at most 20 unless it also clearly follows Portugol-style commands and structure.';
            $prompt[] = 'If the answer is only natural-language description without algorithmic commands, the score must be at most 20.';
            $prompt[] = 'When the selected language is Portugol, evaluate structured algorithmic logic.';
            $prompt[] = 'Do not accept purely descriptive natural-language answers as complete solutions.';
            $prompt[] = 'The solution must present clear input, processing, and output commands, using a structure similar to Portugol or formal pseudocode.';
            $prompt[] = 'Require at least: clear declaration or use of variables; an input command such as leia(...); processing through assignment, condition, or repetition; an output command such as escreva(...); and enough logic to solve all cases in the problem.';
            $prompt[] = 'Accept syntax variations such as leia, ler, entrada, escreva, imprimir, mostrar, se, entao, então, senao, senão, fimse, para, enquanto, faca, faça, fimpara, and fimenquanto.';
            $prompt[] = 'Accept assignments with <-, ←, =, or recebe.';
            $prompt[] = 'Do not penalize missing formal variable declarations when variable use is clear.';
            $prompt[] = 'Penalize vague, incomplete answers that do not make clear how to traverse data, apply conditions, or produce the output.';
            $prompt[] = '';
        }

        if ($startercode !== '') {
            $prompt[] = 'STARTER CODE:';
            $prompt[] = '```' . $language;
            $prompt[] = $startercode;
            $prompt[] = '```';
            $prompt[] = '';
        }

        $prompt[] = 'STUDENT CODE:';
        $prompt[] = '```' . $language;
        $prompt[] = $code;
        $prompt[] = '```';
        $prompt[] = '';
        $prompt[] = 'Be constructive, specific, and do not reveal a full solution.';

        return implode("\n", $prompt);
    }

    /**
     * Normalises the feedback language instruction sent to the provider.
     *
     * @param string $language Moodle language code.
     * @return string Human-readable language instruction.
     */
    private static function normalise_feedback_language(string $language): string {
        $language = strtolower(trim($language));
        $language = str_replace('-', '_', $language);

        $labels = [
            'pt_br' => 'Brazilian Portuguese (pt_br)',
            'pt' => 'Portuguese (pt)',
            'en' => 'English (en)',
            'en_us' => 'English (en_us)',
            'es' => 'Spanish (es)',
            'fr' => 'French (fr)',
        ];

        if (isset($labels[$language])) {
            return $labels[$language];
        }

        if ($language !== '') {
            return $language;
        }

        return 'the same language used by the question text';
    }

    /**
     * Creates a grading record and queues the background task.
     *
     * @param \stdClass $question Loaded question record or definition.
     * @param array $response Question response data.
     * @param int $questionattemptid Quiz question attempt id, if available.
     * @param int $questionattemptstepid Question attempt step id, if available.
     * @param int $userid User id.
     * @return int The created grading record id.
     */
    public static function queue_grading_request(
        \stdClass $question,
        array $response,
        int $questionattemptid = 0,
        int $questionattemptstepid = 0,
        int $userid = 0
    ): int {
        global $DB, $USER;

        $code = (string)($response['code'] ?? '');
        $language = language_helper::normalise((string)($response['language'] ?? ($question->language ?? '')));
        $rubric = (string)($question->rubric ?? '');
        $startercode = (string)($question->startercode ?? '');
        $questiontext = (string)($question->questiontext ?? '');
        $userid = $userid > 0 ? $userid : (int)($USER->id ?? 0);

        if ($userid <= 0) {
            throw new \moodle_exception(
                'error_invalid_userid',
                'qtype_codejudge',
                '',
                null,
                'A valid user id is required to queue a grading request.'
            );
        }

        $feedbacklanguage = self::get_feedback_language_for_user($userid);

        $record = (object) [
            'questionid' => (int)$question->id,
            'questionattemptid' => $questionattemptid > 0 ? $questionattemptid : null,
            'questionattemptstepid' => $questionattemptstepid > 0 ? $questionattemptstepid : null,
            'userid' => $userid,
            'language' => $language,
            'code' => self::normalise_code($code),
            'rubric' => $rubric,
            'prompt' => self::build_prompt($questiontext, $rubric, $language, $code, $startercode, $feedbacklanguage),
            'status' => 'queued',
            'score' => null,
            'feedback' => null,
            'rawresponse' => null,
            'errormessage' => null,
            'gradeapplied' => 0,
            'appliedmark' => null,
            'appliedstate' => null,
            'appliedmessage' => null,
            'timegradeapplied' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        $record->id = $DB->insert_record('qtype_codejudge_grading', $record);

        $task = new \qtype_codejudge\task\grade_submission();
        $task->set_custom_data(['gradingid' => $record->id]);
        \core\task\manager::queue_adhoc_task($task);

        return (int)$record->id;
    }

    /**
     * Queues a task to revisit an existing grading record.
     *
     * @param int $gradingid Grading record id.
     */
    private static function queue_existing_grading_task(int $gradingid): void {
        if ($gradingid <= 0) {
            return;
        }

        $task = new \qtype_codejudge\task\grade_submission();
        $task->set_custom_data(['gradingid' => $gradingid]);
        \core\task\manager::queue_adhoc_task($task);
    }

    /**
     * Queues the latest non-empty response stored for a question attempt.
     *
     * This is used both after final Quiz submission and after immediate or
     * interactive checks, where the relevant response is represented by the
     * latest question attempt step.
     *
     * @param int $questionattemptid Question attempt id.
     * @param int $userid User id fallback when the Quiz attempt cannot be resolved.
     * @return int|null New or existing grading record id.
     */
    public static function queue_latest_response_for_question_attempt(int $questionattemptid, int $userid = 0): ?int {
        global $DB, $USER;

        if ($questionattemptid <= 0) {
            return null;
        }

        $questionattempt = $DB->get_record_sql(
            "SELECT qa.id AS questionattemptid,
                    qa.questionid,
                    qa.questionusageid,
                    q.questiontext,
                    opts.language,
                    opts.rubric,
                    opts.startercode,
                    qat.userid
               FROM {question_attempts} qa
               JOIN {question} q ON q.id = qa.questionid
               JOIN {qtype_codejudge_options} opts ON opts.questionid = q.id
          LEFT JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
              WHERE qa.id = :questionattemptid
                AND q.qtype = :qtype",
            [
                'questionattemptid' => $questionattemptid,
                'qtype' => 'codejudge',
            ]
        );

        if (!$questionattempt) {
            return null;
        }

        $response = self::get_latest_response_step($questionattemptid);
        if (!$response) {
            return null;
        }

        $existing = $DB->get_record('qtype_codejudge_grading', [
            'questionattemptid' => $questionattemptid,
            'questionattemptstepid' => $response->questionattemptstepid,
        ]);

        if ($existing) {
            $lateststate = self::get_latest_question_attempt_state($questionattemptid);
            $isactive = in_array($lateststate, ['todo', 'invalid', 'complete', 'notstarted', 'unprocessed'], true)
                || self::is_quiz_attempt_in_progress($questionattemptid);
            if ($existing->status === 'graded' && empty($existing->gradeapplied) && !$isactive) {
                self::queue_existing_grading_task((int)$existing->id);
            }

            return (int)$existing->id;
        }

        $code = (string)$response->code;
        if (trim($code) === '') {
            return null;
        }

        $resolveduserid = (int)($questionattempt->userid ?? 0);
        if ($resolveduserid <= 0) {
            $resolveduserid = $userid > 0 ? $userid : (int)($USER->id ?? 0);
        }

        $language = (string)($response->language ?? ($questionattempt->language ?? ''));

        return self::queue_grading_request(
            (object) [
                'id' => (int)$questionattempt->questionid,
                'questiontext' => (string)$questionattempt->questiontext,
                'language' => (string)$questionattempt->language,
                'rubric' => (string)$questionattempt->rubric,
                'startercode' => (string)$questionattempt->startercode,
            ],
            [
                'code' => $code,
                'language' => $language,
            ],
            $questionattemptid,
            (int)$response->questionattemptstepid,
            $resolveduserid
        );
    }

    /**
     * Returns the Moodle language code that should be used for feedback.
     *
     * @param int $userid User id.
     * @return string Moodle language code.
     */
    private static function get_feedback_language_for_user(int $userid): string {
        global $CFG, $DB;

        $userlang = '';
        if ($userid > 0) {
            $userlang = (string)$DB->get_field('user', 'lang', ['id' => $userid]);
        }

        if ($userlang !== '') {
            return $userlang;
        }

        if (function_exists('current_language')) {
            $currentlanguage = (string)current_language();
            if ($currentlanguage !== '') {
                return $currentlanguage;
            }
        }

        return (string)($CFG->lang ?? '');
    }

    /**
     * Returns the latest AI grading record for a question attempt.
     *
     * @param int $questionattemptid Question attempt id.
     * @return \stdClass|null
     */
    public static function get_latest_grading_record(int $questionattemptid): ?\stdClass {
        global $DB;

        if ($questionattemptid <= 0) {
            return null;
        }

        $records = $DB->get_records(
            'qtype_codejudge_grading',
            ['questionattemptid' => $questionattemptid],
            'timemodified DESC, id DESC',
            '*',
            0,
            1
        );

        if (!$records) {
            return null;
        }

        return reset($records) ?: null;
    }

    /**
     * Applies an AI grading result to the Moodle question engine as a manual grade.
     *
     * The synchronous question behaviour records a provisional needsgrading
     * state. When the AI result is ready, the safest public API for attaching
     * the final score and feedback remains Moodle's manual grading API.
     *
     * @param \stdClass $record qtype_codejudge_grading record.
     */
    public static function apply_result_to_question_engine(\stdClass $record): void {
        global $CFG, $DB;

        $questionattemptid = (int)($record->questionattemptid ?? 0);
        if ($questionattemptid <= 0 || $record->status !== 'graded' || $record->score === null) {
            self::mark_grade_not_applied($record, 'Missing question attempt id, graded status or score.');
            return;
        }

        if (!self::is_current_response_step($record)) {
            self::mark_grade_not_applied(
                $record,
                'Skipped because this grading result belongs to an older response step.'
            );
            return;
        }

        if (self::is_quiz_attempt_in_progress($questionattemptid)) {
            self::mark_grade_not_applied(
                $record,
                'Skipped because the Quiz attempt is still in progress; the result is stored for inline feedback.'
            );
            return;
        }

        $lateststate = self::get_latest_question_attempt_state($questionattemptid);
        if (in_array($lateststate, ['todo', 'invalid', 'complete', 'notstarted', 'unprocessed'], true)) {
            self::mark_grade_not_applied(
                $record,
                'Skipped because the question attempt is still active; the result is stored for review.'
            );
            return;
        }

        require_once($CFG->dirroot . '/question/engine/lib.php');
        if (file_exists($CFG->dirroot . '/mod/quiz/locallib.php')) {
            require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        }

        $attemptinfo = $DB->get_record_sql("
            SELECT qa.id,
                   qa.questionusageid,
                   qa.slot,
                   qat.quiz,
                   qat.userid,
                   qat.preview
              FROM {question_attempts} qa
         LEFT JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
             WHERE qa.id = :questionattemptid
        ", ['questionattemptid' => $questionattemptid]);

        if (!$attemptinfo) {
            throw new \moodle_exception(
                'error_question_attempt_not_found',
                'qtype_codejudge',
                '',
                null,
                'Question attempt id ' . $questionattemptid . ' was not found.'
            );
        }

        $slot = (int)$attemptinfo->slot;
        $quba = \question_engine::load_questions_usage_by_activity((int)$attemptinfo->questionusageid);
        $maxmark = (float)$quba->get_question_max_mark($slot);
        $mark = round(max(0.0, min(100.0, (float)$record->score)) / 100 * $maxmark, 7);

        $feedbacklanguage = self::get_feedback_language_for_user((int)($record->userid ?? 0));
        $comment = self::format_ai_feedback_for_manual_grade($record, $mark, $maxmark, $feedbacklanguage);
        $quba->manual_grade($slot, $comment, $mark, FORMAT_PLAIN);
        \question_engine::save_questions_usage_by_activity($quba);

        $state = self::get_latest_question_attempt_state($questionattemptid);
        $record->gradeapplied = 1;
        $record->appliedmark = $mark;
        $record->appliedstate = $state;
        $record->appliedmessage = 'Applied via question_usage_by_activity::manual_grade.';
        $record->timegradeapplied = time();
        $record->timemodified = time();
        $DB->update_record('qtype_codejudge_grading', $record);

        self::refresh_quiz_grades($attemptinfo);
        mtrace(
            "qtype_codejudge grade_submission: applied manual grade {$mark}/{$maxmark} " .
            "to question attempt {$questionattemptid}; state is {$state}."
        );
    }

    /**
     * Builds the manual grading comment stored in the question engine.
     *
     * @param \stdClass $record qtype_codejudge_grading record.
     * @param float $mark Mark applied to the question attempt.
     * @param float $maxmark Maximum mark for the question attempt.
     * @param string $feedbacklanguage Moodle language code for comment labels.
     * @return string
     */
    private static function format_ai_feedback_for_manual_grade(
        \stdClass $record,
        float $mark,
        float $maxmark,
        string $feedbacklanguage = ''
    ): string {
        $parts = [];
        $parts[] = get_string('ai_feedback_heading', 'qtype_codejudge', null, $feedbacklanguage);
        $parts[] = get_string('ai_score_line', 'qtype_codejudge', (object)[
            'score' => format_float((float)$record->score, 2),
            'mark' => format_float($mark, 7),
            'maxmark' => format_float($maxmark, 7),
        ], $feedbacklanguage);

        $feedback = trim((string)($record->feedback ?? ''));
        if ($feedback !== '') {
            $parts[] = '';
            $parts[] = $feedback;
        }

        return implode("\n", $parts);
    }

    /**
     * Refreshes the quiz attempt total and gradebook after an automatic manual grade.
     *
     * @param \stdClass $attemptinfo Attempt metadata joined from question_attempts and quiz_attempts.
     */
    private static function refresh_quiz_grades(\stdClass $attemptinfo): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/question/engine/datalib.php');
        if (file_exists($CFG->dirroot . '/mod/quiz/locallib.php')) {
            require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        }

        $uniqueid = (int)$attemptinfo->questionusageid;
        if ($uniqueid > 0) {
            $dm = new \question_engine_data_mapper();
            $DB->execute("
                UPDATE {quiz_attempts}
                   SET sumgrades = ({$dm->sum_usage_marks_subquery('uniqueid')}),
                       timemodified = :timemodified
                 WHERE uniqueid = :uniqueid
            ", [
                'timemodified' => time(),
                'uniqueid' => $uniqueid,
            ]);
        }

        $quizid = (int)($attemptinfo->quiz ?? 0);
        $userid = (int)($attemptinfo->userid ?? 0);
        if ($quizid <= 0 || $userid <= 0 || !class_exists('\mod_quiz\quiz_settings')) {
            return;
        }

        $quizobj = \mod_quiz\quiz_settings::create($quizid, $userid);
        $calculator = $quizobj->get_grade_calculator();
        $calculator->recompute_all_attempt_sumgrades();
        $calculator->recompute_final_grade($userid);

    }

    /**
     * Store why the result was not applied to the question engine.
     *
     * @param \stdClass $record qtype_codejudge_grading record.
     * @param string $message Diagnostic message.
     */
    private static function mark_grade_not_applied(\stdClass $record, string $message): void {
        global $DB;

        if (empty($record->id)) {
            return;
        }

        $record->gradeapplied = 0;
        $record->appliedmessage = $message;
        $record->timemodified = time();
        $DB->update_record('qtype_codejudge_grading', $record);
        mtrace("qtype_codejudge grade_submission: manual grade not applied: {$message}");
    }

    /**
     * Returns the latest question attempt state from the database.
     *
     * @param int $questionattemptid Question attempt id.
     * @return string
     */
    private static function get_latest_question_attempt_state(int $questionattemptid): string {
        global $DB;

        $state = $DB->get_field_sql("
            SELECT state
              FROM {question_attempt_steps}
             WHERE questionattemptid = :questionattemptid
          ORDER BY sequencenumber DESC, id DESC
        ", ['questionattemptid' => $questionattemptid], IGNORE_MULTIPLE);

        return $state === false ? '' : (string)$state;
    }

    /**
     * Checks whether the owning Quiz attempt is still open to student interaction.
     *
     * Applying a manual grade while the Quiz attempt is in progress can change
     * the question usage outside Moodle's normal submit sequence and trigger
     * sequence-check errors when the student navigates to the next page.
     *
     * @param int $questionattemptid Question attempt id.
     * @return bool
     */
    private static function is_quiz_attempt_in_progress(int $questionattemptid): bool {
        global $DB;

        if ($questionattemptid <= 0) {
            return false;
        }

        $state = $DB->get_field_sql("
            SELECT qat.state
              FROM {question_attempts} qa
              JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
             WHERE qa.id = :questionattemptid
        ", ['questionattemptid' => $questionattemptid]);

        return $state === 'inprogress';
    }

    /**
     * Gets the latest real step with non-empty code for a question attempt.
     *
     * @param int $questionattemptid Question attempt id.
     * @return \stdClass|null
     */
    private static function get_latest_response_step(int $questionattemptid): ?\stdClass {
        global $DB;

        $response = $DB->get_record_sql(
            "SELECT qas.id AS questionattemptstepid,
                    qas.sequencenumber,
                    codedata.value AS code,
                    langdata.value AS language
               FROM {question_attempt_steps} qas
               JOIN {question_attempt_step_data} codedata
                 ON codedata.attemptstepid = qas.id
                AND codedata.name = :codename
          LEFT JOIN {question_attempt_step_data} langdata
                 ON langdata.attemptstepid = qas.id
                AND langdata.name = :languagename
              WHERE qas.questionattemptid = :questionattemptid
                AND qas.sequencenumber >= 0
                AND " . $DB->sql_compare_text('codedata.value') . " <> :emptycode
           ORDER BY qas.sequencenumber DESC, qas.id DESC",
            [
                'codename' => 'code',
                'languagename' => 'language',
                'questionattemptid' => $questionattemptid,
                'emptycode' => '',
            ],
            IGNORE_MULTIPLE
        );

        return $response ?: null;
    }

    /**
     * Checks whether a grading record still corresponds to the latest response step.
     *
     * @param \stdClass $record qtype_codejudge_grading record.
     * @return bool
     */
    private static function is_current_response_step(\stdClass $record): bool {
        $questionattemptid = (int)($record->questionattemptid ?? 0);
        $recordstepid = (int)($record->questionattemptstepid ?? 0);
        if ($questionattemptid <= 0 || $recordstepid <= 0) {
            return true;
        }

        $latest = self::get_latest_response_step($questionattemptid);
        if (!$latest) {
            return true;
        }

        return (int)$latest->questionattemptstepid === $recordstepid;
    }
}
