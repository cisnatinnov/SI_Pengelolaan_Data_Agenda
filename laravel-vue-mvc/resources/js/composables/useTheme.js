import { ref } from 'vue';

const THEME_KEY = 'app.theme';

const theme = ref('light');

function applyTheme(mode) {
    theme.value = mode;
    document.documentElement.classList.toggle('dark', mode === 'dark');
    document.documentElement.style.colorScheme = mode;
    localStorage.setItem(THEME_KEY, mode);

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', mode === 'dark' ? '#0f172a' : '#ffffff');
    }
}

function initTheme() {
    const saved = localStorage.getItem(THEME_KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (saved) {
        applyTheme(saved);
    } else {
        applyTheme(prefersDark ? 'dark' : 'light');
    }
}

function toggleTheme() {
    applyTheme(theme.value === 'dark' ? 'light' : 'dark');
}

function init() {
    if (typeof document !== 'undefined' && typeof window !== 'undefined') {
        initTheme();
    }
}

export function useTheme() {
    return {
        theme,
        toggleTheme,
    };
}

export { init };

init();