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
 * Privacy provider for qtype_codejudge.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_codejudge\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Declares, exports and deletes personal data stored by the question type.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns metadata about stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('qtype_codejudge_grading', [
            'questionid' => 'privacy:metadata:qtype_codejudge_grading:questionid',
            'questionattemptid' => 'privacy:metadata:qtype_codejudge_grading:questionattemptid',
            'questionattemptstepid' => 'privacy:metadata:qtype_codejudge_grading:questionattemptstepid',
            'userid' => 'privacy:metadata:qtype_codejudge_grading:userid',
            'language' => 'privacy:metadata:qtype_codejudge_grading:language',
            'code' => 'privacy:metadata:qtype_codejudge_grading:code',
            'rubric' => 'privacy:metadata:qtype_codejudge_grading:rubric',
            'prompt' => 'privacy:metadata:qtype_codejudge_grading:prompt',
            'status' => 'privacy:metadata:qtype_codejudge_grading:status',
            'score' => 'privacy:metadata:qtype_codejudge_grading:score',
            'feedback' => 'privacy:metadata:qtype_codejudge_grading:feedback',
            'rawresponse' => 'privacy:metadata:qtype_codejudge_grading:rawresponse',
            'errormessage' => 'privacy:metadata:qtype_codejudge_grading:errormessage',
            'gradeapplied' => 'privacy:metadata:qtype_codejudge_grading:gradeapplied',
            'appliedmark' => 'privacy:metadata:qtype_codejudge_grading:appliedmark',
            'appliedstate' => 'privacy:metadata:qtype_codejudge_grading:appliedstate',
            'appliedmessage' => 'privacy:metadata:qtype_codejudge_grading:appliedmessage',
            'timegradeapplied' => 'privacy:metadata:qtype_codejudge_grading:timegradeapplied',
            'timecreated' => 'privacy:metadata:qtype_codejudge_grading:timecreated',
            'timemodified' => 'privacy:metadata:qtype_codejudge_grading:timemodified',
        ], 'privacy:metadata:qtype_codejudge_grading');

        $collection->add_external_location_link('ai_provider', [
            'prompt' => 'privacy:metadata:ai_provider:prompt',
            'code' => 'privacy:metadata:ai_provider:code',
            'rubric' => 'privacy:metadata:ai_provider:rubric',
            'feedback' => 'privacy:metadata:ai_provider:feedback',
        ], 'privacy:metadata:ai_provider');

        return $collection;
    }

    /**
     * Gets contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_from_sql(self::context_sql('g.userid = :userid'), self::params(['userid' => $userid]));

        return $contextlist;
    }

    /**
     * Exports user data for approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }

            $records = $DB->get_records_sql(self::records_sql('g.userid = :userid AND ctx.id = :contextid'), self::params([
                'userid' => $userid,
                'contextid' => $context->id,
            ]));

            if (!$records) {
                continue;
            }

            $data = [];
            foreach ($records as $record) {
                $data[] = self::export_record($record);
            }

            $subcontext = [
                get_string('pluginname', 'qtype_codejudge'),
                get_string('privacy:gradingrecords', 'qtype_codejudge'),
            ];
            writer::with_context($context)->export_data($subcontext, (object)[
                'gradingrecords' => $data,
            ]);
        }
    }

    /**
     * Deletes all plugin data in a context.
     *
     * @param \context $context Context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }

        self::delete_records_for_select('ctx.id = :contextid', ['contextid' => $context->id]);
    }

    /**
     * Deletes plugin data for a user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }

            self::delete_records_for_select('g.userid = :userid AND ctx.id = :contextid', [
                'userid' => $userid,
                'contextid' => $context->id,
            ]);
        }
    }

    /**
     * Adds users with data in the supplied context to the userlist.
     *
     * @param userlist $userlist User list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }

        $userlist->add_from_sql('userid', self::users_sql('ctx.id = :contextid'), self::params(['contextid' => $context->id]));
    }

    /**
     * Deletes plugin data for approved users in a context.
     *
     * @param approved_userlist $userlist Approved user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$usersql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
        $params['contextid'] = $context->id;

        self::delete_records_for_select("g.userid {$usersql} AND ctx.id = :contextid", $params);
    }

    /**
     * Converts a grading record to exportable data.
     *
     * @param \stdClass $record Grading record.
     * @return \stdClass
     */
    private static function export_record(\stdClass $record): \stdClass {
        return (object)[
            'id' => (int)$record->id,
            'questionid' => (int)$record->questionid,
            'questionattemptid' => $record->questionattemptid === null ? null : (int)$record->questionattemptid,
            'questionattemptstepid' => $record->questionattemptstepid === null ? null : (int)$record->questionattemptstepid,
            'language' => (string)$record->language,
            'code' => (string)($record->code ?? ''),
            'rubric' => (string)($record->rubric ?? ''),
            'prompt' => (string)($record->prompt ?? ''),
            'status' => (string)$record->status,
            'score' => $record->score === null ? null : (float)$record->score,
            'feedback' => (string)($record->feedback ?? ''),
            'rawresponse' => (string)($record->rawresponse ?? ''),
            'errormessage' => (string)($record->errormessage ?? ''),
            'gradeapplied' => (int)($record->gradeapplied ?? 0),
            'appliedmark' => $record->appliedmark === null ? null : (float)$record->appliedmark,
            'appliedstate' => (string)($record->appliedstate ?? ''),
            'appliedmessage' => (string)($record->appliedmessage ?? ''),
            'timegradeapplied' => empty($record->timegradeapplied) ? null : transform::datetime($record->timegradeapplied),
            'timecreated' => transform::datetime($record->timecreated),
            'timemodified' => transform::datetime($record->timemodified),
        ];
    }

    /**
     * Deletes grading records matching a context-aware SQL predicate.
     *
     * @param string $where SQL predicate using aliases from records_sql().
     * @param array $params Query params.
     */
    private static function delete_records_for_select(string $where, array $params): void {
        global $DB;

        $ids = $DB->get_fieldset_sql(self::ids_sql($where), self::params($params));
        if (!$ids) {
            return;
        }

        $DB->delete_records_list('qtype_codejudge_grading', 'id', $ids);
    }

    /**
     * SQL selecting context ids for grading records.
     *
     * @param string $where SQL predicate.
     * @return string SQL.
     */
    private static function context_sql(string $where): string {
        return "
            SELECT DISTINCT ctx.id
              FROM {qtype_codejudge_grading} g
              JOIN {question_attempts} qa ON qa.id = g.questionattemptid
              JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
              JOIN {quiz} quiz ON quiz.id = qat.quiz
              JOIN {modules} m ON m.name = :modname
              JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = quiz.id
              JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
             WHERE {$where}
        ";
    }

    /**
     * SQL selecting full grading records joined to Quiz contexts.
     *
     * @param string $where SQL predicate.
     * @return string SQL.
     */
    private static function records_sql(string $where): string {
        return "
            SELECT g.*
              FROM {qtype_codejudge_grading} g
              JOIN {question_attempts} qa ON qa.id = g.questionattemptid
              JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
              JOIN {quiz} quiz ON quiz.id = qat.quiz
              JOIN {modules} m ON m.name = :modname
              JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = quiz.id
              JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
             WHERE {$where}
          ORDER BY g.timecreated ASC, g.id ASC
        ";
    }

    /**
     * SQL selecting user ids from grading records joined to Quiz contexts.
     *
     * @param string $where SQL predicate.
     * @return string SQL.
     */
    private static function users_sql(string $where): string {
        return "
            SELECT DISTINCT g.userid
              FROM {qtype_codejudge_grading} g
              JOIN {question_attempts} qa ON qa.id = g.questionattemptid
              JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
              JOIN {quiz} quiz ON quiz.id = qat.quiz
              JOIN {modules} m ON m.name = :modname
              JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = quiz.id
              JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
             WHERE {$where}
        ";
    }

    /**
     * SQL selecting grading record ids joined to Quiz contexts.
     *
     * @param string $where SQL predicate.
     * @return string SQL.
     */
    private static function ids_sql(string $where): string {
        return "
            SELECT g.id
              FROM {qtype_codejudge_grading} g
              JOIN {question_attempts} qa ON qa.id = g.questionattemptid
              JOIN {quiz_attempts} qat ON qat.uniqueid = qa.questionusageid
              JOIN {quiz} quiz ON quiz.id = qat.quiz
              JOIN {modules} m ON m.name = :modname
              JOIN {course_modules} cm ON cm.module = m.id AND cm.instance = quiz.id
              JOIN {context} ctx ON ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
             WHERE {$where}
        ";
    }

    /**
     * Returns common SQL params used by context-aware privacy queries.
     *
     * @return array
     */
    private static function params(array $params = []): array {
        return $params + [
            'modname' => 'quiz',
            'contextlevel' => CONTEXT_MODULE,
        ];
    }
}
