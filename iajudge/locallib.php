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
 * Internal (private) helper functions for mod_iajudge.
 *
 * Functions here are NOT part of the Moodle core API; they are used only
 * within the mod_iajudge plugin. They are loaded by view.php and other
 * files via require_once.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the number of code submissions made by a specific user for a given activity.
 *
 * @param int $iajudgeid The iajudge instance id.
 * @param int $userid    The user id.
 * @return int           Number of submissions.
 */
function iajudge_count_user_submissions(int $iajudgeid, int $userid): int {
    global $DB;
    return $DB->count_records('iajudge_submission', [
        'iajudgeid' => $iajudgeid,
        'userid'     => $userid,
    ]);
}

/**
 * Returns all submissions for a given user in a given activity, joined with grade data.
 *
 * Returns an array of plain arrays (suitable for Mustache templates).
 *
 * @param int $iajudgeid The iajudge instance id.
 * @param int $userid    The user id.
 * @return array         Array of submission data arrays, newest first.
 */
function iajudge_get_user_submissions(int $iajudgeid, int $userid): array {
    global $DB;

    $sql = "SELECT s.id,
                   s.iajudgeid,
                   s.userid,
                   s.language,
                   s.code,
                   s.status,
                   s.timecreated,
                   g.score,
                   g.feedback
              FROM {iajudge_submission} s
         LEFT JOIN {iajudge_grade} g ON g.submissionid = s.id
             WHERE s.iajudgeid = :iajudgeid
               AND s.userid     = :userid
          ORDER BY s.timecreated DESC";

    $records = $DB->get_records_sql($sql, [
        'iajudgeid' => $iajudgeid,
        'userid'    => $userid,
    ]);

    $result = [];
    foreach ($records as $record) {
        $result[] = [
            'id'          => $record->id,
            'language'    => $record->language,
            'code'        => $record->code,
            'status'      => $record->status,
            'timecreated' => userdate($record->timecreated),
            'score'       => $record->score !== null ? number_format((float)$record->score, 1) : null,
            'feedback'    => $record->feedback,
            'isgraded'    => $record->status === 'graded',
            'ispending'   => in_array($record->status, ['pending', 'processing']),
            'iserror'     => $record->status === 'error',
        ];
    }

    return $result;
}

/**
 * Returns all submissions across all users for a given activity (teacher view).
 *
 * @param int $iajudgeid The iajudge instance id.
 * @return array         Array of submission data arrays, newest first.
 */
function iajudge_get_all_submissions(int $iajudgeid): array {
    global $DB;

    $sql = "SELECT s.id,
                   s.userid,
                   s.language,
                   s.code,
                   s.status,
                   s.timecreated,
                   g.score,
                   g.feedback,
                   u.firstname,
                   u.lastname
              FROM {iajudge_submission} s
         LEFT JOIN {iajudge_grade} g ON g.submissionid = s.id
         LEFT JOIN {user} u ON u.id = s.userid
             WHERE s.iajudgeid = :iajudgeid
          ORDER BY s.timecreated DESC";

    $records = $DB->get_records_sql($sql, ['iajudgeid' => $iajudgeid]);

    $result = [];
    foreach ($records as $record) {
        $result[] = [
            'id'          => $record->id,
            'fullname'    => fullname($record),
            'language'    => $record->language,
            'code'        => $record->code,
            'status'      => $record->status,
            'timecreated' => userdate($record->timecreated),
            'score'       => $record->score !== null ? number_format((float)$record->score, 1) : '—',
            'feedback'    => $record->feedback,
            'isgraded'    => $record->status === 'graded',
            'ispending'   => in_array($record->status, ['pending', 'processing']),
            'iserror'     => $record->status === 'error',
        ];
    }

    return $result;
}

/**
 * Returns the allowed languages for a given iajudge instance as an array
 * of ['key' => 'python', 'label' => 'Python'] items for use in Mustache.
 *
 * @param stdClass $iajudge The iajudge record.
 * @return array            Array of language option arrays.
 */
function iajudge_get_allowed_languages(stdClass $iajudge): array {
    $labelmap = [
        'python'     => get_string('lang_python',     'mod_iajudge'),
        'c'          => get_string('lang_c',          'mod_iajudge'),
        'java'       => get_string('lang_java',       'mod_iajudge'),
        'javascript' => get_string('lang_javascript', 'mod_iajudge'),
    ];

    $langs = explode(',', $iajudge->allowed_languages);
    $result = [];
    foreach ($langs as $lang) {
        $lang = trim($lang);
        if ($lang && isset($labelmap[$lang])) {
            $result[] = [
                'key'   => $lang,
                'label' => $labelmap[$lang],
            ];
        }
    }
    return $result;
}

/**
 * Returns a single submission record by id, joined with grade data.
 *
 * @param int $submissionid The submission id.
 * @return stdClass|false   The submission record, or false if not found.
 */
function iajudge_get_submission_with_grade(int $submissionid): stdClass|false {
    global $DB;

    $sql = "SELECT s.*,
                   g.score,
                   g.feedback,
                   g.raw_response
              FROM {iajudge_submission} s
         LEFT JOIN {iajudge_grade} g ON g.submissionid = s.id
             WHERE s.id = :id";

    return $DB->get_record_sql($sql, ['id' => $submissionid]);
}
