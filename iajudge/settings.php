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
 * Global administrator settings for mod_iajudge.
 *
 * Accessible at: Site Administration → Plugins → Activity Modules → IA Judge
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    global $OUTPUT;

    // -----------------------------------------------------------------------
    // Section heading
    // -----------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'mod_iajudge/settings_heading',
        get_string('settings_heading', 'mod_iajudge'),
        get_string('settings_heading_desc', 'mod_iajudge')
    ));

    // -----------------------------------------------------------------------
    // AI Provider selector
    // -----------------------------------------------------------------------
    $providerchoices = [
        'core_ai'   => get_string('provider_core_ai',   'mod_iajudge'),
        'openai'    => get_string('provider_openai',    'mod_iajudge'),
        'anthropic' => get_string('provider_anthropic', 'mod_iajudge'),
        'gemini'    => get_string('provider_gemini',    'mod_iajudge'),
        'ollama'    => get_string('provider_ollama',    'mod_iajudge'),
    ];

    $settings->add(new admin_setting_configselect(
        'mod_iajudge/provider',
        get_string('settings_provider', 'mod_iajudge'),
        get_string('settings_provider_desc', 'mod_iajudge'),
        'openai',       // Default provider.
        $providerchoices
    ));

    // -----------------------------------------------------------------------
    // API Key (password field — masked in the UI)
    // -----------------------------------------------------------------------
    $settings->add(new admin_setting_configpasswordunmask(
        'mod_iajudge/api_key',
        get_string('settings_api_key', 'mod_iajudge'),
        get_string('settings_api_key_desc', 'mod_iajudge'),
        ''              // Default: empty.
    ));

    // -----------------------------------------------------------------------
    // Base URL / Endpoint (required for Ollama and enterprise proxies)
    // -----------------------------------------------------------------------
    $settings->add(new admin_setting_configtext(
        'mod_iajudge/base_url',
        get_string('settings_base_url', 'mod_iajudge'),
        get_string('settings_base_url_desc', 'mod_iajudge'),
        '',             // Default: empty (each provider uses its own default endpoint).
        PARAM_URL
    ));

    // -----------------------------------------------------------------------
    // Model name
    // -----------------------------------------------------------------------
    $settings->add(new admin_setting_configtext(
        'mod_iajudge/model_name',
        get_string('settings_model_name', 'mod_iajudge'),
        get_string('settings_model_name_desc', 'mod_iajudge'),
        'gpt-4o',       // Sensible default.
        PARAM_TEXT
    ));

    // -----------------------------------------------------------------------
    // "Test Connection" widget.
    // The markup is rendered via Mustache, and the actual test logic lives in
    // classes/external/test_connection.php and is called via AMD AJAX from
    // amd/src/settings_test.js.
    // -----------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'mod_iajudge/test_connection_heading',
        get_string('test_connection', 'mod_iajudge'),
        get_string('test_connection_desc', 'mod_iajudge')
            . html_writer::tag(
                'div',
                $OUTPUT->render_from_template('mod_iajudge/admin_settings_test', [
                    'testconnectionlabel' => get_string('test_connection', 'mod_iajudge'),
                    'testconnectionloadinglabel' => get_string('test_connection_testing', 'mod_iajudge'),
                ]),
                ['class' => 'mt-2']
            )
    ));

    // Wire the AMD module that handles the button click.
    // (Runs only when the admin settings page is displayed.)
    global $PAGE;
    if ($PAGE->has_set_url()) {
        $PAGE->requires->js_call_amd('mod_iajudge/settings_test', 'init');
    }
}
