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
 * Library of interface functions and constants for mod_iajudge.
 *
 * All the core Moodle functions, neeeded to integrate with the Moodle framework,
 * are placed here.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// ---------------------------------------------------------------------------
// Moodle core API callbacks
// ---------------------------------------------------------------------------

/**
 * Returns the information on whether the module supports a feature.
 *
 * @param string $feature FEATURE_xx constant for requested feature.
 * @return mixed True if supported, null if unknown, false if not supported.
 */
function iajudge_supports(string $feature): mixed {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        default:
            return null;
    }
}

/**
 * Saves a new instance of mod_iajudge into the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will create a new instance and return the id number of the
 * new instance.
 *
 * @param stdClass $data An object from the form in mod_form.php.
 * @param mod_iajudge_mod_form|null $mform The form instance (if needed).
 * @return int The id of the newly inserted record.
 */
function iajudge_add_instance(stdClass $data, ?mod_iajudge_mod_form $mform = null): int {
    global $DB;

    $data->timecreated  = time();
    $data->timemodified = time();

    // Serialize the allowed_languages array (checkboxgroup) to a CSV string.
    if (!empty($data->allowed_languages) && is_array($data->allowed_languages)) {
        $data->allowed_languages = implode(',', array_filter($data->allowed_languages));
    } else {
        $data->allowed_languages = 'python,c,java,javascript';
    }

    $data->id = $DB->insert_record('iajudge', $data);

    iajudge_grade_item_update($data);

    return $data->id;
}

/**
 * Updates an instance of mod_iajudge in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param stdClass $data An object from the form in mod_form.php.
 * @param mod_iajudge_mod_form|null $mform The form instance (if needed).
 * @return bool True if successful, false otherwise.
 */
function iajudge_update_instance(stdClass $data, ?mod_iajudge_mod_form $mform = null): bool {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    // Serialize the allowed_languages array (checkboxgroup) to a CSV string.
    if (!empty($data->allowed_languages) && is_array($data->allowed_languages)) {
        $data->allowed_languages = implode(',', array_filter($data->allowed_languages));
    } else {
        $data->allowed_languages = 'python,c,java,javascript';
    }

    $result = $DB->update_record('iajudge', $data);

    iajudge_grade_item_update($data);

    return $result;
}

/**
 * Removes an instance of mod_iajudge from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function iajudge_delete_instance(int $id): bool {
    global $DB;

    if (!$instance = $DB->get_record('iajudge', ['id' => $id])) {
        return false;
    }

    // Delete all associated grades.
    $DB->delete_records_select(
        'iajudge_grade',
        'submissionid IN (SELECT id FROM {iajudge_submission} WHERE iajudgeid = ?)',
        [$id]
    );

    // Delete all associated submissions.
    $DB->delete_records('iajudge_submission', ['iajudgeid' => $id]);

    // Delete the activity instance itself.
    $DB->delete_records('iajudge', ['id' => $id]);

    // Remove grade item from Gradebook.
    iajudge_grade_item_delete($instance);

    return true;
}

// ---------------------------------------------------------------------------
// Gradebook API
// ---------------------------------------------------------------------------

/**
 * Creates or updates a grade item for the given iajudge instance.
 *
 * @param stdClass $iajudge Object with fields from iajudge table.
 * @param mixed    $grades  Optional array/object of grades, or GRADE_UPDATE_ITEM_ONLY, or null.
 * @return int GRADE_UPDATE_OK or GRADE_UPDATE_FAILED.
 */
function iajudge_grade_item_update(stdClass $iajudge, mixed $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $item = [
        'itemname'  => clean_param($iajudge->name, PARAM_NOTAGS),
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax'  => 100,
        'grademin'  => 0,
    ];

    if ($grades === 'reset') {
        $item['reset'] = true;
        $grades = null;
    }

    return grade_update(
        'mod/iajudge',
        $iajudge->course,
        'mod',
        'iajudge',
        $iajudge->id,
        0,
        $grades,
        $item
    );
}

/**
 * Deletes grade item for the given iajudge instance.
 *
 * @param stdClass $iajudge Object with fields from iajudge table.
 * @return int GRADE_UPDATE_OK or GRADE_UPDATE_FAILED.
 */
function iajudge_grade_item_delete(stdClass $iajudge): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update(
        'mod/iajudge',
        $iajudge->course,
        'mod',
        'iajudge',
        $iajudge->id,
        0,
        null,
        ['deleted' => 1]
    );
}

/**
 * Update iajudge grades in the gradebook.
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param stdClass $iajudge Instance object with extra cmidnumber and modname property.
 * @param int      $userid  Update grade of specific user only, 0 means all participants.
 */
function iajudge_update_grades(stdClass $iajudge, int $userid = 0): void {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    $grades = [];

    $sql = "SELECT s.userid, g.score
              FROM {iajudge_submission} s
              JOIN {iajudge_grade} g ON g.submissionid = s.id
             WHERE s.iajudgeid = :iajudgeid";
    $params = ['iajudgeid' => $iajudge->id];

    if ($userid) {
        $sql   .= ' AND s.userid = :userid';
        $params['userid'] = $userid;
    }

    $records = $DB->get_records_sql($sql, $params);

    foreach ($records as $record) {
        $grades[$record->userid] = [
            'userid'   => $record->userid,
            'rawgrade' => $record->score,
        ];
    }

    if (empty($grades)) {
        iajudge_grade_item_update($iajudge);
    } else {
        iajudge_grade_item_update($iajudge, $grades);
    }
}
