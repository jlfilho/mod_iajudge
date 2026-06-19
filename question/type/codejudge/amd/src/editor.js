/**
 * Code editor enhancement for qtype_codejudge.
 *
 * @package     qtype_codejudge
 * @copyright   2026 IA Judge Contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['require'], function(require) {
    var TAB = '    ';

    var getRoot = function(rootid) {
        if (!rootid) {
            return null;
        }

        if (typeof rootid === 'string') {
            return document.getElementById(rootid);
        }

        return rootid;
    };

    var getTextarea = function(root) {
        return root ? root.querySelector('[data-region="codejudge-code"]') : null;
    };

    var getLabel = function(root, textarea) {
        if (!root || !textarea || !textarea.id) {
            return '';
        }

        var label = root.querySelector('label[for="' + textarea.id + '"]');
        return label ? label.textContent : '';
    };

    var resize = function(textarea) {
        if (!textarea) {
            return;
        }

        textarea.style.height = 'auto';
        textarea.style.height = Math.max(textarea.scrollHeight, textarea.offsetHeight, 220) + 'px';
    };

    var indentSelection = function(textarea, shiftKey) {
        var start = textarea.selectionStart;
        var end = textarea.selectionEnd;
        var value = textarea.value;
        var selected = value.substring(start, end);

        if (!shiftKey) {
            var replacement = selected || TAB;
            if (selected) {
                replacement = selected.replace(/^/gm, TAB);
            }

            textarea.value = value.substring(0, start) + replacement + value.substring(end);
            textarea.selectionStart = start + TAB.length;
            textarea.selectionEnd = end + TAB.length;
            return;
        }

        var unindented = selected.replace(new RegExp('^' + TAB.replace(/ /g, '\\s'), 'gm'), '');
        unindented = unindented.replace(/^ {1,4}/gm, '');

        textarea.value = value.substring(0, start) + unindented + value.substring(end);
        textarea.selectionStart = Math.max(start - TAB.length, 0);
        textarea.selectionEnd = Math.max(start - TAB.length + unindented.length, 0);
    };

    var initialiseTextareaFallback = function(textarea) {
        textarea.spellcheck = false;
        textarea.setAttribute('wrap', 'off');
        textarea.setAttribute('autocomplete', 'off');
        textarea.setAttribute('autocorrect', 'off');
        textarea.setAttribute('autocapitalize', 'off');

        if (!textarea.hasAttribute('readonly')) {
            textarea.addEventListener('keydown', function(event) {
                if (event.key !== 'Tab') {
                    return;
                }

                event.preventDefault();
                indentSelection(textarea, event.shiftKey);
                resize(textarea);
            });

            textarea.addEventListener('input', function() {
                resize(textarea);
            });

            textarea.addEventListener('focus', function() {
                resize(textarea);
            });
        }

        resize(textarea);
    };

    var initCodeMirror = function(root, textarea) {
        require(['qtype_codejudge/codemirror6'], function(CodeMirror6) {
            if (!CodeMirror6 || typeof CodeMirror6.create !== 'function') {
                return;
            }

            try {
                root.codejudgeEditor = CodeMirror6.create(textarea, {
                    ariaLabel: getLabel(root, textarea),
                    language: textarea.dataset.language || root.dataset.language || 'python',
                    minHeight: textarea.dataset.editorHeight || textarea.style.minHeight
                });
                root.dataset.codejudgeEditor = 'codemirror6';
            } catch (error) {
                root.dataset.codejudgeEditor = 'textarea';
                resize(textarea);
            }
        }, function() {
            root.dataset.codejudgeEditor = 'textarea';
            resize(textarea);
        });
    };

    var initEditor = function(rootid) {
        var root = getRoot(rootid);
        var textarea;

        if (!root || root.dataset.codejudgeInitialized === '1') {
            return;
        }

        textarea = getTextarea(root);
        if (!textarea) {
            return;
        }

        root.dataset.codejudgeInitialized = '1';
        root.dataset.codejudgeEditor = 'textarea';
        initialiseTextareaFallback(textarea);
        initCodeMirror(root, textarea);
    };

    return {
        init: initEditor
    };
});
