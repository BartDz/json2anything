import { EditorView, basicSetup } from 'https://esm.sh/codemirror@6.0.1';
import { json } from 'https://esm.sh/@codemirror/lang-json@6';
import { oneDark } from 'https://esm.sh/@codemirror/theme-one-dark@6';

const outputEl  = document.getElementById('output');
const tabItems  = document.querySelectorAll('.tabs li');
const copyBtn   = document.getElementById('copy');

let currentFormat = 'yaml';
let debounceTimer = null;

const editor = new EditorView({
    doc: '',
    extensions: [
        basicSetup,
        json(),
        oneDark,
        EditorView.updateListener.of(update => {
            if (update.docChanged) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(convert, 300);
            }
        }),
    ],
    parent: document.getElementById('editor'),
});

tabItems.forEach(li => {
    li.addEventListener('click', () => {
        tabItems.forEach(t => t.classList.remove('is-active'));
        li.classList.add('is-active');
        currentFormat = li.dataset.format;
        convert();
    });
});

async function convert() {
    const value = editor.state.doc.toString().trim();
    if (!value) {
        outputEl.textContent = '';
        document.getElementById('editor').classList.remove('error');
        return;
    }

    try {
        JSON.parse(value);
    } catch (e) {
        const match = e.message.match(/position (\d+)/i);
        let errorMsg = e.message;
        if (match) {
            const pos    = parseInt(match[1]);
            const before = value.substring(0, pos);
            const line   = (before.match(/\n/g) || []).length + 1;
            const col    = pos - before.lastIndexOf('\n');
            errorMsg     = `JSON error at line ${line}, col ${col}: ${e.message}`;
        }
        outputEl.textContent = errorMsg;
        document.getElementById('editor').classList.remove('error');
        void document.getElementById('editor').offsetWidth;
        document.getElementById('editor').classList.add('error');
        return;
    }

    document.getElementById('editor').classList.remove('error');

    try {
        const res = await fetch('/convert', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ input: value, from: 'json', to: currentFormat }),
        });
        const data = await res.json();
        outputEl.textContent = data.output ?? data.error;
    } catch {
        outputEl.textContent = 'Network error';
    }
}

copyBtn.addEventListener('click', () => {
    const text = outputEl.textContent;
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        copyBtn.textContent = 'Copied!';
        copyBtn.classList.add('copied');
        setTimeout(() => {
            copyBtn.textContent = 'Copy';
            copyBtn.classList.remove('copied');
        }, 2000);
    });
});

document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        convert();
    }
});
