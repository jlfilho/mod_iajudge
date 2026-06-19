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
 * Unit tests for the grading helper.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/codejudge/classes/local/grading_helper.php');

/**
 * Tests for grading helper prompt assembly.
 */
class qtype_codejudge_grading_helper_testcase extends advanced_testcase {

    public function test_build_prompt_includes_key_sections(): void {
        $prompt = \qtype_codejudge\local\grading_helper::build_prompt(
            '<p>Explain the result.</p>',
            'Use clear variable names.',
            'python',
            "print('hi')",
            'print("starter")',
            'pt_br'
        );

        $this->assertStringContainsString('QUESTION:', $prompt);
        $this->assertStringContainsString('Explain the result.', $prompt);
        $this->assertStringContainsString('RUBRIC:', $prompt);
        $this->assertStringContainsString('PROGRAMMING LANGUAGE: Python', $prompt);
        $this->assertStringContainsString('LANGUAGE-COMPLIANCE RULE:', $prompt);
        $this->assertStringContainsString('The expected language for this answer is: Python.', $prompt);
        $this->assertStringContainsString('Grade language compliance before algorithm correctness.', $prompt);
        $this->assertStringContainsString('the score must be at most 30', $prompt);
        $this->assertStringContainsString('the score must be at most 20', $prompt);
        $this->assertStringContainsString('Brazilian Portuguese (pt_br)', $prompt);
        $this->assertStringContainsString('STARTER CODE:', $prompt);
        $this->assertStringContainsString('STUDENT CODE:', $prompt);
        $this->assertStringContainsString('```python', $prompt);
    }

    public function test_normalise_code_converts_line_endings(): void {
        $code = \qtype_codejudge\local\grading_helper::normalise_code("line1\r\nline2\rline3");
        $this->assertSame("line1\nline2\nline3", $code);
    }

    public function test_build_prompt_includes_portugol_specific_rules(): void {
        $prompt = \qtype_codejudge\local\grading_helper::build_prompt(
            'Conte as vogais de uma palavra.',
            'Avalie a logica.',
            'portugol',
            "leia palavra\ncontador <- 0\nescreva contador",
            '',
            'pt_br'
        );

        $this->assertStringContainsString('PROGRAMMING LANGUAGE: Portugol', $prompt);
        $this->assertStringContainsString('PORTUGOL-SPECIFIC GRADING RULES:', $prompt);
        $this->assertStringContainsString('Language compliance is mandatory', $prompt);
        $this->assertStringContainsString('the score must be at most 30', $prompt);
        $this->assertStringContainsString('executable code in another programming language', $prompt);
        $this->assertStringContainsString('the score must be at most 20', $prompt);
        $this->assertStringContainsString('Do not accept purely descriptive natural-language answers', $prompt);
        $this->assertStringContainsString('leia', $prompt);
        $this->assertStringContainsString('escreva', $prompt);
        $this->assertStringContainsString('```portugol', $prompt);
    }
}
