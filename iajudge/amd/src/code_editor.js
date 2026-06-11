/**
 * Monaco Editor integration for mod_iajudge.
 *
 * @package     mod_iajudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    return {
        /**
         * Initialize the Monaco Editor on the submission page.
         *
         * @param {Object} config Configuration object containing:
         *                        - cmid: Course module ID.
         *                        - languages: Array of allowed language keys.
         *                        - monacobase: Base URL to Monaco's min/vs folder.
         */
        init: function(config) {
            // Configure RequireJS path for Monaco.
            require.config({
                paths: {
                    'vs': config.monacobase
                }
            });

            // Load the main Monaco module.
            require(['vs/editor/editor.main'], function() {
                var editorContainer = document.getElementById('iajudge_editor');
                if (!editorContainer) {
                    return;
                }

                var hiddenCodeInput = document.getElementById('id_code');
                var languageSelect = document.getElementById('id_language');
                var submitError = document.getElementById('ia-judge-submit-error');
                var initialLanguage = languageSelect ? languageSelect.value : 'python';

                // Map Moodle language keys to Monaco language keys if they differ.
                var languageMap = {
                    'python': 'python',
                    'c': 'c',
                    'java': 'java',
                    'javascript': 'javascript'
                };

                var monacoLanguage = languageMap[initialLanguage] || 'plaintext';

                // Initialize the editor.
                var editor = monaco.editor.create(editorContainer, {
                    value: hiddenCodeInput ? hiddenCodeInput.value : '',
                    language: monacoLanguage,
                    theme: 'vs-dark',
                    automaticLayout: true,
                    lineNumbers: 'on',
                    fontSize: 14,
                    tabSize: 4,
                    minimap: {
                        enabled: false
                    },
                    scrollBeyondLastLine: false,
                    roundedSelection: true,
                    cursorBlinking: 'smooth'
                });

                // Update Monaco language mode when the user changes the select dropdown.
                if (languageSelect) {
                    $(languageSelect).on('change', function() {
                        var selectedVal = $(this).val();
                        var targetLang = languageMap[selectedVal] || 'plaintext';
                        var model = editor.getModel();
                        if (model) {
                            monaco.editor.setModelLanguage(model, targetLang);
                        }
                    });
                }

                // Handle form submission.
                var submitButton = $('#iajudge_submit_btn');
                var submitButtonSpinner = submitButton.find('.ia-judge-submit-spinner');
                var submitButtonIcon = submitButton.find('.ia-judge-submit-icon');
                var submitButtonLabel = submitButton.find('.ia-judge-submit-label');
                var form = $('#iajudge_submission_form');

                form.on('submit', function(e) {
                    var codeValue = editor.getValue();
                    if (!codeValue || codeValue.trim() === '') {
                        e.preventDefault();
                        if (submitError) {
                            submitError.textContent = M.util.get_string('error_empty_code', 'mod_iajudge');
                            submitError.classList.remove('d-none');
                        }
                        editor.focus();
                        return false;
                    }

                    if (submitError) {
                        submitError.textContent = '';
                        submitError.classList.add('d-none');
                    }

                    // Store code in hidden input so Moodle form processes it.
                    if (hiddenCodeInput) {
                        hiddenCodeInput.value = codeValue;
                    }

                    // Disable button and editor to prevent double submission.
                    if (submitButton) {
                        submitButton.prop('disabled', true).addClass('disabled');
                        if (submitButtonSpinner.length) {
                            submitButtonSpinner.removeClass('d-none');
                        }
                        if (submitButtonIcon.length) {
                            submitButtonIcon.addClass('d-none');
                        }
                        if (submitButtonLabel.length) {
                            submitButtonLabel.text(M.util.get_string('status_sending', 'mod_iajudge'));
                        }
                    }
                    editor.updateOptions({ readOnly: true });
                });
            });
        }
    };
});
