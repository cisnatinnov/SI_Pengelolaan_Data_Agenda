/**
 * The app is always rendered in light (day) mode. Dark mode toggling has been
 * removed; this only guarantees the `dark` class is never present.
 */
function init() {
    if (typeof document !== 'undefined') {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
    }
}

export { init };
