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
 * Tests for codejudge backup/restore definitions.
 *
 * These tests intentionally check the plugin backup files as integration
 * contracts. Instantiating Moodle backup controllers is too heavy for a qtype
 * unit test, but these assertions catch accidental removal of required fields.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Static contract tests for backup/restore support.
 */
class qtype_codejudge_backup_restore_testcase extends advanced_testcase {

    public function test_backup_exports_question_options_only(): void {
        global $CFG;

        $source = file_get_contents(
            $CFG->dirroot . '/question/type/codejudge/backup/moodle2/backup_qtype_codejudge_plugin.class.php'
        );

        $this->assertStringContainsString('qtype_codejudge_options', $source);
        $this->assertStringContainsString("'language'", $source);
        $this->assertStringContainsString("'rubric'", $source);
        $this->assertStringContainsString("'startercode'", $source);
        $this->assertStringContainsString("'editorheight'", $source);
        $this->assertStringNotContainsString('qtype_codejudge_grading', $source);
        $this->assertStringNotContainsString("'code'", $source);
        $this->assertStringNotContainsString("'prompt'", $source);
        $this->assertStringNotContainsString("'rawresponse'", $source);
    }

    public function test_restore_imports_question_options_for_new_question_id(): void {
        global $CFG;

        $source = file_get_contents(
            $CFG->dirroot . '/question/type/codejudge/backup/moodle2/restore_qtype_codejudge_plugin.class.php'
        );

        $this->assertStringContainsString('/qtype_codejudge_options', $source);
        $this->assertStringContainsString("get_new_parentid('question')", $source);
        $this->assertStringContainsString('insert_record', $source);
        $this->assertStringContainsString('set_mapping', $source);
    }
}
