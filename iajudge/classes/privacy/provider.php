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
 * Privacy API implementation for mod_iajudge.
 *
 * Declares what personal data the plugin stores and provides the mandatory
 * methods for exporting and deleting user data in compliance with GDPR
 * (General Data Protection Regulation).
 *
 * Data held by this plugin:
 *  - mdl_iajudge_submission: userid, language, code, timecreated.
 *  - mdl_iajudge_grade: score, feedback (linked to submission → user).
 *  - External transfer: code is sent to the configured AI provider API.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_iajudge\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for mod_iajudge.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    // -----------------------------------------------------------------------
    // Metadata
    // -----------------------------------------------------------------------

    /**
     * Describes the personal data stored by this plugin.
     *
     * @param  collection $collection Metadata collection to append to.
     * @return collection             Updated collection.
     */
    public static function get_metadata(collection $collection): collection {

        // Submissions table.
        $collection->add_database_table(
            'iajudge_submission',
            [
                'userid'      => 'privacy:metadata:iajudge_submission:userid',
                'language'    => 'privacy:metadata:iajudge_submission:language',
                'code'        => 'privacy:metadata:iajudge_submission:code',
                'timecreated' => 'privacy:metadata:iajudge_submission:timecreated',
            ],
            'privacy:metadata:iajudge_submission'
        );

        // Grades table (linked to submissions, hence to users).
        $collection->add_database_table(
            'iajudge_grade',
            [
                'score'    => 'privacy:metadata:iajudge_grade:score',
                'feedback' => 'privacy:metadata:iajudge_grade:feedback',
            ],
            'privacy:metadata:iajudge_grade'
        );

        // External AI provider disclosure.
        $collection->add_external_location_link(
            'external_ai_provider',
            [
                'code'     => 'privacy:metadata:iajudge_submission:code',
                'language' => 'privacy:metadata:iajudge_submission:language',
            ],
            'privacy:metadata:external_ai_provider'
        );

        return $collection;
    }

    // -----------------------------------------------------------------------
    // Context discovery
    // -----------------------------------------------------------------------

    /**
     * Returns the list of contexts that contain personal data for the given user.
     *
     * @param  int         $userid The user's ID.
     * @return contextlist         Contexts where this user has data.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                       AND ctx.contextlevel = :contextlevel
                  JOIN {iajudge} ij ON ij.id = cm.instance
                  JOIN {iajudge_submission} s ON s.iajudgeid = ij.id
                 WHERE s.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'userid'       => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Returns a userlist of users who have data within the given context.
     *
     * @param userlist $userlist The userlist to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $sql = "SELECT s.userid
                  FROM {iajudge_submission} s
                  JOIN {course_modules} cm ON cm.instance = s.iajudgeid
                 WHERE cm.id = :cmid";

        $userlist->add_from_sql('userid', $sql, ['cmid' => $context->instanceid]);
    }

    // -----------------------------------------------------------------------
    // Data export
    // -----------------------------------------------------------------------

    /**
     * Exports all personal data for the given user in the given contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts to export.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm      = get_coursemodule_from_id('iajudge', $context->instanceid);
            $iajudge = $DB->get_record('iajudge', ['id' => $cm->instance]);

            $sql = "SELECT s.id,
                           s.language,
                           s.code,
                           s.status,
                           s.timecreated,
                           g.score,
                           g.feedback
                      FROM {iajudge_submission} s
                 LEFT JOIN {iajudge_grade} g ON g.submissionid = s.id
                     WHERE s.iajudgeid = :iajudgeid
                       AND s.userid = :userid
                  ORDER BY s.timecreated";

            $submissions = $DB->get_records_sql($sql, [
                'iajudgeid' => $iajudge->id,
                'userid'    => $userid,
            ]);

            $exportdata = [];
            foreach ($submissions as $sub) {
                $exportdata[] = [
                    'language'    => $sub->language,
                    'code'        => $sub->code,
                    'status'      => $sub->status,
                    'timecreated' => transform::datetime($sub->timecreated),
                    'score'       => $sub->score,
                    'feedback'    => $sub->feedback,
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'mod_iajudge')],
                (object) ['submissions' => $exportdata]
            );
        }
    }

    // -----------------------------------------------------------------------
    // Data deletion
    // -----------------------------------------------------------------------

    /**
     * Deletes all personal data for all users in the given context.
     *
     * @param \context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        $cm      = get_coursemodule_from_id('iajudge', $context->instanceid);
        $iajudge = $DB->get_record('iajudge', ['id' => $cm->instance]);

        if (!$iajudge) {
            return;
        }

        // Delete grades first (FK constraint).
        $DB->delete_records_select(
            'iajudge_grade',
            'submissionid IN (SELECT id FROM {iajudge_submission} WHERE iajudgeid = ?)',
            [$iajudge->id]
        );

        $DB->delete_records('iajudge_submission', ['iajudgeid' => $iajudge->id]);
    }

    /**
     * Deletes all personal data for the specified user in the given contexts.
     *
     * @param approved_contextlist $contextlist Contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm      = get_coursemodule_from_id('iajudge', $context->instanceid);
            $iajudge = $DB->get_record('iajudge', ['id' => $cm->instance]);

            if (!$iajudge) {
                continue;
            }

            // Delete grades for this user's submissions.
            $DB->delete_records_select(
                'iajudge_grade',
                'submissionid IN (
                    SELECT id FROM {iajudge_submission}
                    WHERE iajudgeid = ? AND userid = ?
                )',
                [$iajudge->id, $userid]
            );

            $DB->delete_records('iajudge_submission', [
                'iajudgeid' => $iajudge->id,
                'userid'     => $userid,
            ]);
        }
    }

    /**
     * Deletes personal data for users given an approved userlist.
     *
     * @param approved_userlist $userlist The userlist to delete data for.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm      = get_coursemodule_from_id('iajudge', $context->instanceid);
        $iajudge = $DB->get_record('iajudge', ['id' => $cm->instance]);

        if (!$iajudge) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);

        $DB->delete_records_select(
            'iajudge_grade',
            "submissionid IN (
                SELECT id FROM {iajudge_submission}
                WHERE iajudgeid = :iajudgeid AND userid {$insql}
             )",
            array_merge(['iajudgeid' => $iajudge->id], $inparams)
        );

        $DB->delete_records_select(
            'iajudge_submission',
            "iajudgeid = :iajudgeid AND userid {$insql}",
            array_merge(['iajudgeid' => $iajudge->id], $inparams)
        );
    }
}
