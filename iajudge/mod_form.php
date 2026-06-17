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
 * Activity module form for mod_iajudge.
 *
 * Teachers use this form when adding or editing an iajudge activity in a course.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/iajudge/locallib.php');

/**
 * Module instance settings form.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_iajudge_mod_form extends moodleform_mod {

    /**
     * Defines the form fields for the teacher configuration UI.
     */
    public function definition(): void {
        global $CFG, $COURSE;

        $mform = $this->_form;

        // ---------------------------------------------------------------
        // Section: General
        // ---------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Activity name — added automatically by parent class via add_intro_editor().
        $this->standard_intro_elements();
        // The intro field is the "Problem Statement" (uses the native Moodle rich text editor).
        $mform->setAdvanced('showdescription', false);

        // ---------------------------------------------------------------
        // Section: AI Evaluation Settings
        // ---------------------------------------------------------------
        $mform->addElement('header', 'ai_settings_header', get_string('pluginname', 'mod_iajudge'));

        // Correction rubric (AI instructions) — large textarea.
        $mform->addElement(
            'textarea',
            'rubric_prompt',
            get_string('rubric_prompt', 'mod_iajudge'),
            ['rows' => 10, 'cols' => 80, 'style' => 'width:100%; font-family: monospace;']
        );
        $mform->setType('rubric_prompt', PARAM_TEXT);
        $mform->addHelpButton('rubric_prompt', 'rubric_prompt', 'mod_iajudge');
        $mform->addRule('rubric_prompt', null, 'required', null, 'client');
        $mform->setDefault('rubric_prompt', get_string('rubric_prompt_placeholder', 'mod_iajudge'));

        // ---------------------------------------------------------------
        // Allowed programming languages — checkboxes group.
        // ---------------------------------------------------------------
        $mform->addElement('header', 'languages_header', get_string('allowed_languages', 'mod_iajudge'));

        $languages = [
            'python'     => get_string('lang_python',     'mod_iajudge'),
            'c'          => get_string('lang_c',          'mod_iajudge'),
            'java'       => get_string('lang_java',       'mod_iajudge'),
            'javascript' => get_string('lang_javascript', 'mod_iajudge'),
        ];

        $checkboxes = [];
        foreach ($languages as $key => $label) {
            // Submit the values as allowed_languages[python|c|java|javascript]
            // so Moodle returns a single array for validation and saving.
            $checkboxes[] = $mform->createElement('checkbox', "allowed_languages[$key]", '', $label);
        }

        $mform->addGroup(
            $checkboxes,
            'allowed_languages',
            get_string('allowed_languages', 'mod_iajudge'),
            ['<br>'],
            false
        );
        $mform->addHelpButton('allowed_languages', 'allowed_languages', 'mod_iajudge');

        // Default: all languages selected.
        foreach (array_keys($languages) as $key) {
            $mform->setDefault("allowed_languages[$key]", 1);
        }

        // ---------------------------------------------------------------
        // Coding questions from the question bank.
        // ---------------------------------------------------------------
        $mform->addElement('header', 'questions_header', get_string('question_bank', 'mod_iajudge'));

        $availablequestions = iajudge_get_codejudge_bank_questions((int)$COURSE->id);
        if (empty($availablequestions)) {
            $mform->addElement(
                'static',
                'no_codejudge_questions',
                '',
                get_string('no_codejudge_questions', 'mod_iajudge')
            );
        } else {
            foreach ($availablequestions as $question) {
                $summary = trim(strip_tags(format_text(
                    $question->questiontext ?? '',
                    $question->questiontextformat ?? FORMAT_HTML,
                    ['context' => context_course::instance((int)$COURSE->id)]
                )));
                if (core_text::strlen($summary) > 220) {
                    $summary = core_text::substr($summary, 0, 220) . '...';
                }

                $label = html_writer::div(
                    html_writer::tag('strong', format_string($question->name)) .
                    html_writer::div(
                        get_string('question_default_mark', 'mod_iajudge', format_float((float)$question->defaultmark, 1)),
                        'small text-muted'
                    ) .
                    ($summary !== '' ? html_writer::div(s($summary), 'small') : ''),
                    'mb-2'
                );

                $elements = [];
                $elements[] = $mform->createElement('advcheckbox', "selected_questions[$question->id]", '', '');
                $elements[] = $mform->createElement('static', '', '', $label);
                $mform->addGroup($elements, "selectedquestiongroup{$question->id}", '', [' '], false);
            }
        }

        // ---------------------------------------------------------------
        // Submission limits
        // ---------------------------------------------------------------
        $mform->addElement('header', 'submission_header', get_string('max_attempts', 'mod_iajudge'));

        $attemptsoptions = [0 => get_string('unlimited', 'mod_iajudge')];
        for ($i = 1; $i <= 20; $i++) {
            $attemptsoptions[$i] = $i;
        }

        $mform->addElement(
            'select',
            'max_attempts',
            get_string('max_attempts', 'mod_iajudge'),
            $attemptsoptions
        );
        $mform->setType('max_attempts', PARAM_INT);
        $mform->addHelpButton('max_attempts', 'max_attempts', 'mod_iajudge');
        $mform->setDefault('max_attempts', 0);

        // ---------------------------------------------------------------
        // Standard course module elements (grading, visibility, etc.)
        // ---------------------------------------------------------------
        $this->standard_coursemodule_elements();

        // ---------------------------------------------------------------
        // Action buttons
        // ---------------------------------------------------------------
        $this->add_action_buttons();
    }

    /**
     * Performs additional validation before saving the form data.
     *
     * @param array $data  Submitted form data.
     * @param array $files Uploaded files (unused here).
     * @return array Associative array of field => error message.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        // Ensure at least one language is selected.
        $selected = array_filter($data['allowed_languages'] ?? []);
        if (empty($selected)) {
            $errors['allowed_languages'] = get_string('allowed_languages_help', 'mod_iajudge');
        }

        // Rubric must not be empty.
        if (empty(trim($data['rubric_prompt'] ?? ''))) {
            $errors['rubric_prompt'] = get_string('error_empty_rubric', 'mod_iajudge');
        }

        if (empty(array_filter($data['selected_questions'] ?? []))) {
            $errors['selected_questions'] = get_string('error_no_questions_selected', 'mod_iajudge');
        }

        return $errors;
    }

    /**
     * Preprocess the data before it is set into the form (edit mode).
     *
     * Converts the CSV allowed_languages string back to the checkbox array format.
     *
     * @param array $defaultvalues Form default values from DB.
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);

        if (!empty($defaultvalues['allowed_languages'])) {
            $langs = explode(',', $defaultvalues['allowed_languages']);
            $defaultvalues['allowed_languages'] = [];
            foreach ($langs as $lang) {
                $lang = trim($lang);
                if ($lang) {
                    $defaultvalues['allowed_languages'][$lang] = 1;
                }
            }
        }

        if (!empty($defaultvalues['instance'])) {
            $selected = iajudge_get_activity_questions((int)$defaultvalues['instance']);
            $defaultvalues['selected_questions'] = [];
            foreach ($selected as $question) {
                $defaultvalues['selected_questions'][$question->questionid] = 1;
            }
        }
    }
}
