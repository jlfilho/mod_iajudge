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
 * Unit tests for the codejudge question type class.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/codejudge/questiontype.php');

/**
 * Tests for qtype_codejudge persistence behaviour.
 */
class qtype_codejudge_questiontype_testcase extends advanced_testcase {

    public function test_extra_question_fields_match_options_table(): void {
        $qtype = new qtype_codejudge();

        $this->assertSame([
            'qtype_codejudge_options',
            'language',
            'rubric',
            'startercode',
            'editorheight',
        ], $qtype->extra_question_fields());
    }

    public function test_save_question_options_inserts_and_normalises_values(): void {
        global $DB;

        $this->resetAfterTest();
        $qtype = new qtype_codejudge();
        $question = (object)[
            'id' => 100001,
            'language' => 'rust',
            'rubric' => '  Check correctness.  ',
            'startercode' => "print('start')",
            'editorheight' => 100,
        ];

        $this->assertTrue($qtype->save_question_options($question));

        $record = $DB->get_record('qtype_codejudge_options', ['questionid' => $question->id], '*', MUST_EXIST);
        $this->assertSame('python', $record->language);
        $this->assertSame('Check correctness.', $record->rubric);
        $this->assertSame("print('start')", $record->startercode);
        $this->assertSame(420, (int)$record->editorheight);
    }

    public function test_save_question_options_updates_existing_record(): void {
        global $DB;

        $this->resetAfterTest();
        $qtype = new qtype_codejudge();
        $question = (object)[
            'id' => 100002,
            'language' => 'python',
            'rubric' => 'Initial rubric.',
            'startercode' => '',
            'editorheight' => 420,
        ];
        $qtype->save_question_options($question);

        $question->language = 'portugol';
        $question->rubric = 'Updated rubric.';
        $question->startercode = 'leia valor';
        $question->editorheight = 640;

        $this->assertTrue($qtype->save_question_options($question));

        $records = $DB->get_records('qtype_codejudge_options', ['questionid' => $question->id]);
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertSame('portugol', $record->language);
        $this->assertSame('Updated rubric.', $record->rubric);
        $this->assertSame('leia valor', $record->startercode);
        $this->assertSame(640, (int)$record->editorheight);
    }

    public function test_get_question_options_supplies_safe_defaults_without_record(): void {
        $this->resetAfterTest();
        $qtype = new qtype_codejudge();
        $question = (object)['id' => 100003];

        $this->assertTrue($qtype->get_question_options($question));

        $this->assertSame('python', $question->language);
        $this->assertSame('', $question->rubric);
        $this->assertSame('', $question->startercode);
        $this->assertSame(420, $question->editorheight);
        $this->assertSame(100003, (int)$question->options->questionid);
    }
}
