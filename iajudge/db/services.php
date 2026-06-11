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
 * External functions (Web Services) registration for mod_iajudge.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [

    // Returns the current status and result of a given submission.
    // Called by the AMD polling module (submission_status.js) after a student submits code.
    'mod_iajudge_get_submission_status' => [
        'classname'     => \mod_iajudge\external\get_submission_status::class,
        'methodname'    => 'execute',
        'description'   => 'Returns the evaluation status and result (score + feedback) for a submission.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],

    // Tests the currently configured AI provider connection.
    // Called by the AMD settings_test.js module from the admin settings page.
    'mod_iajudge_test_connection' => [
        'classname'     => \mod_iajudge\external\test_connection::class,
        'methodname'    => 'execute',
        'description'   => 'Tests the connection to the configured AI provider and returns success or error details.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'moodle/site:config',
    ],

];
