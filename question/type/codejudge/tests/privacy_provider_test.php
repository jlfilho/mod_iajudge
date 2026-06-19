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
 * Unit tests for the codejudge privacy provider contract.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/codejudge/classes/privacy/provider.php');

/**
 * Tests for qtype_codejudge\privacy\provider.
 */
class qtype_codejudge_privacy_provider_testcase extends advanced_testcase {

    public function test_provider_implements_required_privacy_interfaces(): void {
        $interfaces = class_implements(\qtype_codejudge\privacy\provider::class);

        $this->assertContains(\core_privacy\local\metadata\provider::class, $interfaces);
        $this->assertContains(\core_privacy\local\request\plugin\provider::class, $interfaces);
        $this->assertContains(\core_privacy\local\request\core_userlist_provider::class, $interfaces);
    }

    public function test_provider_no_longer_claims_null_provider_contract(): void {
        $interfaces = class_implements(\qtype_codejudge\privacy\provider::class);

        $this->assertNotContains(\core_privacy\local\metadata\null_provider::class, $interfaces);
    }

    public function test_metadata_declares_grading_table_and_ai_provider(): void {
        $collection = new \core_privacy\local\metadata\collection('qtype_codejudge');
        $collection = \qtype_codejudge\privacy\provider::get_metadata($collection);
        $items = $collection->get_collection();
        $names = [];

        foreach ($items as $item) {
            if (method_exists($item, 'get_name')) {
                $names[] = $item->get_name();
            }
        }

        $this->assertContains('qtype_codejudge_grading', $names);
        $this->assertContains('ai_provider', $names);
    }
}
