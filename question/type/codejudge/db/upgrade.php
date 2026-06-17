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
 * Upgrade steps for qtype_codejudge.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion The previous version.
 * @return bool
 */
function xmldb_qtype_codejudge_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026061300) {
        $table = new xmldb_table('qtype_codejudge_grading');

        if (!$dbman->table_exists($table)) {
            $table->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
            $table->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null));
            $table->addField(new xmldb_field('questionattemptid', XMLDB_TYPE_INTEGER, '10', null, null, null, null));
            $table->addField(new xmldb_field('questionattemptstepid', XMLDB_TYPE_INTEGER, '10', null, null, null, null));
            $table->addField(new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null));
            $table->addField(new xmldb_field('language', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'python'));
            $table->addField(new xmldb_field('code', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('rubric', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('prompt', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'queued'));
            $table->addField(new xmldb_field('score', XMLDB_TYPE_NUMBER, '5,2', null, null, null, null));
            $table->addField(new xmldb_field('feedback', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('rawresponse', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('errormessage', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));

            $table->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']));
            $table->addKey(new xmldb_key('fk_questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'question', ['id']));
            $table->addKey(new xmldb_key('fk_userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']));
            $table->addIndex(new xmldb_index('idx_questionid', XMLDB_INDEX_NOTUNIQUE, ['questionid']));
            $table->addIndex(new xmldb_index('idx_questionattemptid', XMLDB_INDEX_NOTUNIQUE, ['questionattemptid']));
            $table->addIndex(new xmldb_index('idx_status', XMLDB_INDEX_NOTUNIQUE, ['status']));

            $dbman->create_table($table);
        }

        $table = new xmldb_table('qtype_codejudge_options');

        if (!$dbman->table_exists($table)) {
            $table->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
            $table->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null));
            $table->addField(new xmldb_field('language', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'python'));
            $table->addField(new xmldb_field('rubric', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('startercode', XMLDB_TYPE_TEXT, null, null, null, null, null));
            $table->addField(new xmldb_field('editorheight', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '420'));
            $table->addField(new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));

            $table->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']));
            $table->addKey(new xmldb_key('fk_questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'question', ['id']));
            $table->addIndex(new xmldb_index('uq_questionid', XMLDB_INDEX_UNIQUE, ['questionid']));

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026061300, 'qtype', 'codejudge');
    }

    return true;
}
