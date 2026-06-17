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
 * Upgrade steps for mod_iajudge.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute module upgrade steps.
 *
 * This initial skeleton keeps the plugin upgrade-ready and follows the Moodle
 * 5.2 Upgrade API structure. Add future schema/data migrations here.
 *
 * @param int $oldversion The version from which the upgrade is starting.
 * @return bool
 */
function xmldb_mod_iajudge_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026061102) {
        upgrade_mod_savepoint(true, 2026061102, 'iajudge');
    }

    if ($oldversion < 2026061200) {
        $table = new xmldb_table('iajudge_question');

        if (!$dbman->table_exists($table)) {
            $table->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
            $table->addField(new xmldb_field('iajudgeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null));
            $table->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null));
            $table->addField(new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));

            $table->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, ['id']));
            $table->addKey(new xmldb_key('fk_iajudgeid', XMLDB_KEY_FOREIGN, ['iajudgeid'], 'iajudge', ['id']));
            $table->addKey(new xmldb_key('fk_questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'question', ['id']));
            $table->addIndex(new xmldb_index('uq_iajudge_question', XMLDB_INDEX_UNIQUE, ['iajudgeid', 'questionid']));
            $table->addIndex(new xmldb_index('idx_iajudge_sort', XMLDB_INDEX_NOTUNIQUE, ['iajudgeid', 'sortorder']));

            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2026061200, 'iajudge');
    }

    return true;
}
