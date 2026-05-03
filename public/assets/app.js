const inputEl  = document.getElementById('input');
const outputEl = document.getElementById('output');
const tabItems = document.querySelectorAll('.tabs li');
const copyBtn  = document.getElementById('copy');

let currentFormat = 'yaml';
let debounceTimer = null;

tabItems.forEach(li => {
    li.addEventListener('click', () => {
        tabItems.forEach(t => t.classList.remove('is-active'));
        li.classList.add('is-active');
        currentFormat = li.dataset.format;
        convert();
    });
});

inputEl.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(convert, 300);
});

async function convert() {
    const value = inputEl.value.trim();
    if (!value) {
        outputEl.textContent = '';
        inputEl.classList.remove('error');
        return;
    }

    try {
        JSON.parse(value);
    } catch (e) {
        outputEl.textContent = e.message;
        inputEl.classList.remove('error');
        void inputEl.offsetWidth;
        inputEl.classList.add('error');
        return;
    }

    inputEl.classList.remove('error');

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
