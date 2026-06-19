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
 * Renderer for coding questions.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer that provides the code editor and question controls.
 */
class qtype_codejudge_renderer extends qtype_renderer {

    /**
     * Render the question formulation and response control.
     *
     * @param question_attempt $qa Question attempt.
     * @param question_display_options $options Display options.
     * @return string
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options): string {
        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();
        $fieldname = $qa->get_qt_field_name('code');
        $current = trim((string)($response['code'] ?? ''));

        if ($current === '') {
            $current = (string)($question->startercode ?? '');
        }

        $height = (int)($question->editorheight ?? 420);
        $rootid = uniqid('qtype-codejudge-');
        $language = \qtype_codejudge\local\language_helper::normalise($question->language ?? null);
        $languageoptions = \qtype_codejudge\local\language_helper::get_options();
        $languagedescription = \qtype_codejudge\local\language_helper::get_description($language);
        $questionattemptid = method_exists($qa, 'get_database_id') ? (int)$qa->get_database_id() : 0;
        $showinlinefeedback = $this->uses_inline_feedback_behaviour($qa);

        $textareaattributes = [
            'id' => $rootid . '-code',
            'name' => $fieldname,
            'class' => 'form-control qtype-codejudge-editor',
            'rows' => 12,
            'style' => 'min-height:' . $height . 'px; font-family: monospace;',
            'spellcheck' => 'false',
            'autocapitalize' => 'off',
            'autocomplete' => 'off',
            'autocorrect' => 'off',
            'data-region' => 'codejudge-code',
            'data-language' => $language,
            'data-editor-height' => $height,
            'aria-label' => get_string('responseeditor', 'qtype_codejudge'),
        ];

        if ($options->readonly) {
            $textareaattributes['readonly'] = 'readonly';
            $textareaattributes['data-readonly'] = '1';
        }

        $html = [];
        $html[] = html_writer::tag(
            'div',
            $question->format_questiontext($qa),
            ['class' => 'qtext']
        );
        $html[] = html_writer::tag(
            'div',
            get_string('allowedlanguage', 'qtype_codejudge') . ': ' . s($languageoptions[$language] ?? $language),
            ['class' => 'badge bg-light text-dark mb-2']
        );
        $html[] = html_writer::tag(
            'div',
            get_string('editor_help', 'qtype_codejudge'),
            ['class' => 'small text-muted mb-2']
        );
        if ($languagedescription !== '') {
            $html[] = html_writer::tag(
                'div',
                s($languagedescription),
                ['class' => 'small text-muted mb-2 qtype-codejudge-language-description']
            );
        }
        $html[] = html_writer::tag('label', get_string('responseeditor', 'qtype_codejudge'), [
            'class' => 'font-weight-bold mb-2',
            'for' => $rootid . '-code',
        ]);
        $html[] = html_writer::tag('textarea', s($current), $textareaattributes);
        $html[] = html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $qa->get_qt_field_name('language'),
            'value' => $language,
        ]);
        $this->queue_latest_interactive_response($qa, $questionattemptid);
        $html[] = $this->grading_status($questionattemptid, $showinlinefeedback);

        $this->page->requires->js_call_amd('qtype_codejudge/editor', 'init', [$rootid]);

        return html_writer::tag('div', implode('', $html), [
            'class' => 'qtype-codejudge-formulation',
            'id' => $rootid,
            'data-region' => 'codejudge-root',
            'data-questionid' => (int)$question->id,
            'data-questionattemptid' => $questionattemptid,
            'data-language' => $language,
        ]);
    }

    /**
     * Render the AI grading state for review/teacher views.
     *
     * @param int $questionattemptid Question attempt id.
     * @param bool $showinlinefeedback Whether to show the correction comment in the question body.
     * @return string
     */
    private function grading_status(int $questionattemptid, bool $showinlinefeedback = false): string {
        if ($questionattemptid <= 0) {
            return '';
        }

        $record = \qtype_codejudge\local\grading_helper::get_latest_grading_record($questionattemptid);
        if (!$record) {
            return '';
        }

        $status = (string)($record->status ?? 'queued');
        $statusstrings = [
            'graded' => get_string('grading_status_graded', 'qtype_codejudge'),
            'error' => get_string('grading_status_error', 'qtype_codejudge'),
            'processing' => get_string('grading_status_processing', 'qtype_codejudge'),
            'queued' => get_string('grading_status_queued', 'qtype_codejudge'),
        ];
        $classes = [
            'graded' => 'alert alert-success mt-3 mb-0',
            'error' => 'alert alert-danger mt-3 mb-0',
            'processing' => 'alert alert-info mt-3 mb-0',
            'queued' => 'alert alert-secondary mt-3 mb-0',
        ];

        $content = [];
        $content[] = html_writer::tag(
            'strong',
            get_string('ai_grading_status', 'qtype_codejudge') . ': '
        ) . s($statusstrings[$status] ?? $statusstrings['queued']);

        if ($status === 'graded' && $record->score !== null) {
            $content[] = html_writer::tag(
                'div',
                get_string('ai_score_percent', 'qtype_codejudge', format_float((float)$record->score, 2)),
                ['class' => 'mt-1']
            );
            if (!empty($record->gradeapplied)) {
                $content[] = html_writer::tag(
                    'div',
                    get_string('ai_grade_applied', 'qtype_codejudge'),
                    ['class' => 'small mt-1']
                );
            } else {
                $message = trim((string)($record->appliedmessage ?? ''));
                $content[] = html_writer::tag(
                    'div',
                    get_string('ai_grade_not_applied', 'qtype_codejudge') . ($message !== '' ? ' ' . s($message) : ''),
                    ['class' => 'small mt-1']
                );
            }

            $feedback = trim((string)($record->feedback ?? ''));
            if ($showinlinefeedback && $feedback !== '') {
                $content[] = html_writer::tag(
                    'div',
                    html_writer::tag('strong', get_string('ai_feedback_heading', 'qtype_codejudge')),
                    ['class' => 'mt-2']
                );
                $content[] = html_writer::tag(
                    'div',
                    format_text($feedback, FORMAT_PLAIN),
                    ['class' => 'mt-1 qtype-codejudge-inline-feedback']
                );
            }

        }

        if ($status === 'error' && !empty($record->errormessage)) {
            $content[] = html_writer::tag('div', s($record->errormessage), ['class' => 'small mt-1']);
        }

        return html_writer::tag('div', implode('', $content), [
            'class' => $classes[$status] ?? $classes['queued'],
            'data-region' => 'codejudge-grading-status',
        ]);
    }

    /**
     * Queue the current response step when an immediate/interactive check has just been rendered.
     *
     * Rendering is the first safe point where the qtype can observe the new step
     * created by the question behaviour without intercepting Quiz submission.
     *
     * @param question_attempt $qa Question attempt.
     * @param int $questionattemptid Question attempt id.
     */
    private function queue_latest_interactive_response(question_attempt $qa, int $questionattemptid): void {
        global $USER;

        if ($questionattemptid <= 0) {
            return;
        }

        if (!$this->uses_inline_feedback_behaviour($qa)) {
            return;
        }

        try {
            \qtype_codejudge\local\grading_helper::queue_latest_response_for_question_attempt(
                $questionattemptid,
                (int)($USER->id ?? 0)
            );
        } catch (\Throwable $e) {
            debugging('qtype_codejudge could not queue interactive grading: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Whether this question attempt is using a behaviour that should show formative feedback inline.
     *
     * @param question_attempt $qa Question attempt.
     * @return bool
     */
    private function uses_inline_feedback_behaviour(question_attempt $qa): bool {
        try {
            $behaviour = method_exists($qa, 'get_behaviour_name') ? (string)$qa->get_behaviour_name() : '';
        } catch (\Throwable $e) {
            return false;
        }

        return in_array($behaviour, ['immediatefeedback', 'interactive'], true);
    }
}
