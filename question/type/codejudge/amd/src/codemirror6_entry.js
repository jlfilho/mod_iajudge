// This file is bundled into amd/src/codemirror6.js and amd/build/codemirror6.min.js.
// It is not loaded directly by Moodle.

import {autocompletion, closeBrackets, closeBracketsKeymap, completionKeymap} from '@codemirror/autocomplete';
import {
    defaultKeymap,
    history,
    historyKeymap,
    indentWithTab,
} from '@codemirror/commands';
import {
    bracketMatching,
    defaultHighlightStyle,
    foldGutter,
    foldKeymap,
    indentOnInput,
    StreamLanguage,
    syntaxHighlighting,
} from '@codemirror/language';
import {cpp} from '@codemirror/lang-cpp';
import {java} from '@codemirror/lang-java';
import {javascript} from '@codemirror/lang-javascript';
import {php} from '@codemirror/lang-php';
import {python} from '@codemirror/lang-python';
import {sql} from '@codemirror/lang-sql';
import {searchKeymap, highlightSelectionMatches} from '@codemirror/search';
import {EditorState} from '@codemirror/state';
import {
    crosshairCursor,
    drawSelection,
    dropCursor,
    EditorView,
    highlightActiveLine,
    highlightActiveLineGutter,
    keymap,
    lineNumbers,
    rectangularSelection,
} from '@codemirror/view';

const TAB = '    ';

const portugol = StreamLanguage.define({
    token(stream) {
        if (stream.match(/\/\/.*/)) {
            return 'comment';
        }

        if (stream.match(/"(?:[^"\\]|\\.)*"/)) {
            return 'string';
        }

        if (stream.match(/\d+(?:[.,]\d+)?/)) {
            return 'number';
        }

        if (stream.match(/[A-Za-zÀ-ÿ_][A-Za-zÀ-ÿ0-9_]*/)) {
            const word = stream.current().toLowerCase();
            if (/^(algoritmo|var|inicio|fim|fimalgoritmo|leia|ler|entrada|escreva|imprimir|mostrar|se|entao|então|senao|senão|fimse|para|de|ate|até|passo|enquanto|faca|faça|fimpara|fimenquanto|repita|fimrepita|caso|escolha|outrocaso|fimescolha|verdadeiro|falso|e|ou|nao|não)$/.test(word)) {
                return 'keyword';
            }

            if (/^(inteiro|real|logico|lógico|caractere|cadeia|literal|vetor|matriz)$/.test(word)) {
                return 'typeName';
            }

            return 'variableName';
        }

        stream.next();
        return null;
    },
    languageData: {
        commentTokens: {line: '//'},
    },
});

const languageExtensions = {
    c: cpp(),
    cpp: cpp(),
    java: java(),
    javascript: javascript(),
    php: php(),
    portugol: portugol,
    python: python(),
    sql: sql(),
};

const theme = EditorView.theme({
    '&': {
        border: '1px solid #8f959e',
        borderRadius: '4px',
        backgroundColor: '#fff',
        color: '#1f2933',
        fontSize: '0.95rem',
    },
    '&.cm-focused': {
        outline: '0',
        borderColor: '#0f6cbf',
        boxShadow: '0 0 0 .2rem rgba(15, 108, 191, .25)',
    },
    '.cm-scroller': {
        fontFamily: 'SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace',
        lineHeight: '1.5',
    },
    '.cm-content': {
        padding: '10px 0',
    },
    '.cm-line': {
        padding: '0 12px 0 8px',
    },
    '.cm-gutters': {
        backgroundColor: '#f7f8fa',
        color: '#586069',
        borderRight: '1px solid #d5d8dc',
    },
    '.cm-activeLine': {
        backgroundColor: '#eef6ff',
    },
    '.cm-activeLineGutter': {
        backgroundColor: '#e3f0ff',
    },
    '.cm-foldGutter span': {
        cursor: 'pointer',
    },
    '&.qtype-codejudge-readonly .cm-content': {
        backgroundColor: '#f8f9fa',
    },
});

const buildExtensions = (textarea, options) => {
    const readonly = Boolean(options.readonly);
    const language = languageExtensions[options.language] || languageExtensions.python;

    return [
        lineNumbers(),
        foldGutter(),
        highlightActiveLineGutter(),
        history(),
        drawSelection(),
        dropCursor(),
        EditorState.allowMultipleSelections.of(true),
        indentOnInput(),
        syntaxHighlighting(defaultHighlightStyle, {fallback: true}),
        bracketMatching(),
        closeBrackets(),
        autocompletion(),
        rectangularSelection(),
        crosshairCursor(),
        highlightActiveLine(),
        highlightSelectionMatches(),
        language,
        theme,
        EditorState.tabSize.of(4),
        EditorState.readOnly.of(readonly),
        EditorView.editable.of(!readonly),
        EditorView.lineWrapping,
        EditorView.updateListener.of((update) => {
            if (!update.docChanged) {
                return;
            }

            textarea.value = update.state.doc.toString();
            textarea.dispatchEvent(new Event('input', {bubbles: true}));
        }),
        keymap.of([
            indentWithTab,
            ...closeBracketsKeymap,
            ...defaultKeymap,
            ...historyKeymap,
            ...foldKeymap,
            ...completionKeymap,
            ...searchKeymap,
        ]),
    ];
};

const syncToTextarea = (view, textarea) => {
    textarea.value = view.state.doc.toString();
};

const create = (textarea, options = {}) => {
    const wrapper = document.createElement('div');
    const minHeight = Number.parseInt(options.minHeight || textarea.dataset.editorHeight || '420', 10) || 420;
    const readonly = textarea.hasAttribute('readonly') || textarea.dataset.readonly === '1';
    const language = textarea.dataset.language || options.language || 'python';

    wrapper.className = 'qtype-codejudge-codemirror';
    wrapper.style.minHeight = `${minHeight}px`;
    wrapper.dataset.language = language;

    textarea.parentNode.insertBefore(wrapper, textarea.nextSibling);
    textarea.classList.add('qtype-codejudge-textarea-fallback');
    textarea.style.display = 'none';

    const view = new EditorView({
        parent: wrapper,
        state: EditorState.create({
            doc: textarea.value || '',
            extensions: buildExtensions(textarea, {language, readonly}),
        }),
    });

    view.dom.classList.toggle('qtype-codejudge-readonly', readonly);
    view.dom.setAttribute('aria-label', options.ariaLabel || textarea.getAttribute('aria-label') || '');
    view.dom.style.minHeight = `${minHeight}px`;
    view.scrollDOM.style.minHeight = `${minHeight}px`;

    const form = textarea.form;
    if (form) {
        form.addEventListener('submit', () => syncToTextarea(view, textarea));
    }

    return {
        view,
        destroy() {
            syncToTextarea(view, textarea);
            view.destroy();
            wrapper.remove();
            textarea.style.display = '';
            textarea.classList.remove('qtype-codejudge-textarea-fallback');
        },
        sync() {
            syncToTextarea(view, textarea);
        },
    };
};

export {create, TAB};
