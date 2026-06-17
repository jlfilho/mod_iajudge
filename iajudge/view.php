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
 * Main entry point for mod_iajudge.
 *
 * Handles:
 * - Displaying the Monaco Editor submission form to students.
 * - Receiving submitted code and dispatching an Ad-hoc Task for AI evaluation.
 * - Showing past submissions and AI results to students and teachers.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/iajudge/lib.php');
require_once($CFG->dirroot . '/mod/iajudge/locallib.php');

// ---------------------------------------------------------------------------
// Bootstrap: resolve context from the course module id (cmid).
// ---------------------------------------------------------------------------
$id     = required_param('id', PARAM_INT);   // Course module ID (cmid).
$action = optional_param('action', 'view', PARAM_ALPHA);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'iajudge');
$iajudge = $DB->get_record('iajudge', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/iajudge:view', $context);

// ---------------------------------------------------------------------------
// Page setup.
// ---------------------------------------------------------------------------
$PAGE->set_url('/mod/iajudge/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($iajudge->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('ia-judge-view');

// Mark as viewed (completion tracking).
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// ---------------------------------------------------------------------------
// Handle code submission (POST).
// ---------------------------------------------------------------------------
if ($action === 'submit' && has_capability('mod/iajudge:submit', $context)) {
    require_sesskey();

    $language = required_param('language', PARAM_ALPHA);
    $code     = required_param('code', PARAM_RAW);

    // Validate language is among the allowed ones.
    $allowedlangs = array_column(iajudge_get_allowed_languages($iajudge), 'key');
    if (!in_array($language, $allowedlangs, true)) {
        throw new moodle_exception('error_no_language', 'mod_iajudge');
    }

    // Validate code is not empty.
    if (trim($code) === '') {
        throw new moodle_exception('error_empty_code', 'mod_iajudge');
    }

    // Check attempt limits.
    if ($iajudge->max_attempts > 0) {
        $count = iajudge_count_user_submissions($iajudge->id, $USER->id);
        if ($count >= $iajudge->max_attempts) {
            throw new moodle_exception('no_attempts_remaining', 'mod_iajudge');
        }
    }

    // Save submission to database.
    $submission = new stdClass();
    $submission->iajudgeid  = $iajudge->id;
    $submission->userid      = $USER->id;
    $submission->language    = $language;
    $submission->code        = $code;
    $submission->status      = 'pending';
    $submission->timecreated = time();
    $submission->id          = $DB->insert_record('iajudge_submission', $submission);

    // Dispatch an ad-hoc Task to process the submission asynchronously.
    $task = new \mod_iajudge\task\grade_submission();
    $task->set_custom_data(['submissionid' => $submission->id]);
    \core\task\manager::queue_adhoc_task($task, true);

    // Respond with a JSON payload for AJAX, or redirect for standard POST.
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // AJAX request from AMD module.
        header('Content-Type: application/json');
        echo json_encode([
            'success'      => true,
            'submissionid' => $submission->id,
            'message'      => get_string('submission_received', 'mod_iajudge'),
        ]);
        die();
    }

    // Standard form POST — redirect back to view.
    redirect(
        new moodle_url('/mod/iajudge/view.php', ['id' => $cm->id]),
        get_string('submission_received', 'mod_iajudge'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// ---------------------------------------------------------------------------
// Determine which view to render.
// ---------------------------------------------------------------------------
$isgrader = has_capability('mod/iajudge:viewallsubmissions', $context);

// Load the user's own submissions.
$mysubmissions = iajudge_get_user_submissions($iajudge->id, $USER->id);

// Determine the allowed languages for the dropdown.
$allowedlangs = iajudge_get_allowed_languages($iajudge);
$activityquestions = iajudge_get_activity_questions($iajudge->id);

// Check if the student has remaining attempts.
$attemptsremaining = null;
if (!$isgrader && $iajudge->max_attempts > 0) {
    $used              = count($mysubmissions);
    $attemptsremaining = max(0, $iajudge->max_attempts - $used);
}

$cansubmit = !$isgrader
    && has_capability('mod/iajudge:submit', $context)
    && ($iajudge->max_attempts === 0 || $attemptsremaining > 0);

// ---------------------------------------------------------------------------
// Render output.
// ---------------------------------------------------------------------------
echo $OUTPUT->header();

// Render the appropriate template.
$allsubmissions = [];
if ($isgrader) {
    $allsubmissions = iajudge_get_all_submissions($iajudge->id);
}

$templatecontext = [
    'cmid'              => $cm->id,
    'sesskey'           => sesskey(),
    'iajudgeid'         => $iajudge->id,
    'cansubmit'         => $cansubmit,
    'isgrader'          => $isgrader,
    'allowedlangs'      => array_values($allowedlangs),
    'activityquestions' => array_values($activityquestions),
    'mysubmissions'     => array_values($mysubmissions),
    'allsubmissions'     => array_values($allsubmissions),
    'attemptsremaining' => $attemptsremaining,
    'wwwroot'           => $CFG->wwwroot,
];

echo $OUTPUT->render_from_template('mod_iajudge/view', $templatecontext);

// If the student can submit, initialise the Monaco Editor AMD module.
if ($cansubmit) {
    $PAGE->requires->js_call_amd(
        'mod_iajudge/code_editor',
        'init',
        [
            'cmid'         => $cm->id,
            'languages'    => array_column($allowedlangs, 'key'),
            'monacobase'   => $CFG->wwwroot . '/mod/iajudge/vendor/monaco-editor/min/vs',
        ]
    );
}

// Initialise the submission status polling AMD module (for pending submissions).
$pendingsubmissions = array_filter($mysubmissions, fn($s) => in_array($s['status'], ['pending', 'processing']));
if (!empty($pendingsubmissions)) {
    $PAGE->requires->js_call_amd(
        'mod_iajudge/submission_status',
        'init',
        [
            'submissionids' => array_column(array_values($pendingsubmissions), 'id'),
            'pollinterval'  => 5000, // Poll every 5 seconds.
        ]
    );
}

echo $OUTPUT->footer();
