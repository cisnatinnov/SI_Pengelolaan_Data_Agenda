const STORAGE_KEY = 'data-agenda-theme';

let isDark = false;

function apply(theme) {
    isDark = theme === 'dark';
    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
    }
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch (err) {
        // Ignore storage errors (e.g. private browsing).
    }
}

function init() {
    if (typeof document === 'undefined') return;

    let theme = null;
    try {
        theme = localStorage.getItem(STORAGE_KEY);
    } catch (err) {
        // Ignore storage errors.
    }

    if (theme !== 'light' && theme !== 'dark') {
        theme = window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    apply(theme);
}

function toggle() {
    apply(isDark ? 'light' : 'dark');
}

function useTheme() {
    return { isDark: () => isDark, init, toggle };
}

export { init, toggle, useTheme };