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
 * External function: get_submission_status
 *
 * Returns the current evaluation status and result for a given submission ID.
 * Called by the AMD polling module (submission_status.js) after a student
 * submits code, to update the UI without a full page reload.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_iajudge\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function that returns the status and grade of a submission.
 */
class get_submission_status extends external_api {

    /**
     * Describes the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'submissionid' => new external_value(
                PARAM_INT,
                'The ID of the submission to check.',
                VALUE_REQUIRED
            ),
        ]);
    }

    /**
     * Returns the current status and evaluation result for a submission.
     *
     * @param  int   $submissionid The submission ID.
     * @return array               Associative array matching execute_returns().
     * @throws \moodle_exception   On access denied or submission not found.
     */
    public static function execute(int $submissionid): array {
        global $DB, $USER, $CFG;

        require_once($CFG->dirroot . '/mod/iajudge/locallib.php');

        // Validate and clean parameters.
        ['submissionid' => $submissionid] = self::validate_parameters(
            self::execute_parameters(),
            ['submissionid' => $submissionid]
        );

        // Load submission.
        $submission = $DB->get_record('iajudge_submission', ['id' => $submissionid]);

        if (!$submission) {
            throw new \moodle_exception('error_submission_not_found', 'mod_iajudge');
        }

        // Load the activity instance and validate context.
        $iajudge = $DB->get_record('iajudge', ['id' => $submission->iajudgeid], '*', MUST_EXIST);
        [$course, $cm] = get_course_and_cm_from_instance($iajudge->id, 'iajudge');
        $context = \context_module::instance($cm->id);

        self::validate_context($context);

        // Access control: students may only see their own submissions.
        // Teachers / admins may see any submission.
        $canviewall = has_capability('mod/iajudge:viewallsubmissions', $context);

        if (!$canviewall && (int)$submission->userid !== (int)$USER->id) {
            throw new \moodle_exception('error_access_denied', 'mod_iajudge');
        }

        // Load grade if available.
        $grade = $DB->get_record('iajudge_grade', ['submissionid' => $submissionid]);

        return [
            'submissionid' => $submissionid,
            'status'       => $submission->status,
            'score'        => $grade ? (float) $grade->score : null,
            'feedback'     => $grade ? (string) $grade->feedback : null,
            'isgraded'     => $submission->status === 'graded',
            'iserror'      => $submission->status === 'error',
        ];
    }

    /**
     * Describes the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'submissionid' => new external_value(PARAM_INT,   'Submission ID.'),
            'status'       => new external_value(PARAM_ALPHA, 'Current status: pending|processing|graded|error.'),
            'score'        => new external_value(PARAM_FLOAT, 'Numeric score 0–100 (null until graded).', VALUE_OPTIONAL, null),
            'feedback'     => new external_value(PARAM_RAW,   'Pedagogical feedback text (null until graded).', VALUE_OPTIONAL, null),
            'isgraded'     => new external_value(PARAM_BOOL,  'True if the submission has been graded.'),
            'iserror'      => new external_value(PARAM_BOOL,  'True if evaluation failed with an error.'),
        ]);
    }
}
