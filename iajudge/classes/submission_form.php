<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Submission form for students.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_iajudge;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Student submission form.
 */
class submission_form extends \moodleform {

    /**
     * Define the form elements.
     */
    protected function definition() {
        $mform = $this->_form;

        // Custom action url/attributes can be passed or set.
        $mform->disable_form_change_checker();

        // Pass available languages via custom data.
        $languages = $this->_customdata['languages'] ?? [];
        $options = [];
        foreach ($languages as $lang) {
            $options[$lang['key']] = $lang['label'];
        }

        $mform->addElement('select', 'language', get_string('select_language', 'mod_iajudge'), $options, [
            'id' => 'id_language'
        ]);
        $mform->setType('language', PARAM_ALPHA);
        $mform->addRule('language', null, 'required', null, 'client');

        // Hidden element to store the code submitted from Monaco.
        $mform->addElement('hidden', 'code', '', ['id' => 'id_code']);
        $mform->setType('code', PARAM_RAW);
        $mform->addRule('code', null, 'required', null, 'client');

        // Hidden course module id.
        $mform->addElement('hidden', 'id', $this->_customdata['cmid']);
        $mform->setType('id', PARAM_INT);

        // Hidden action parameter.
        $mform->addElement('hidden', 'action', 'submit');
        $mform->setType('action', PARAM_ALPHA);

        // Submit button.
        $this->add_action_buttons(false, get_string('submit_code', 'mod_iajudge'));
    }
}
