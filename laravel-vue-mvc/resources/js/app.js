import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import { init } from './composables/useTheme';

init();

if ('serviceWorker' in navigator) {
    if (import.meta.env.PROD) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    } else {
        // In dev, remove any previously registered service worker and its caches
        // so stale responses are never served.
        navigator.serviceWorker.getRegistrations().then((registrations) => {
            registrations.forEach((registration) => registration.unregister());
        });
        caches.keys().then((keys) => keys.forEach((key) => caches.delete(key)));
    }
}

const app = createApp(App);

app.mount('#app');