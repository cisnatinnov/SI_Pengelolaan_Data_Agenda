import { reactive } from 'vue';

const toasts = reactive([]);
let nextId = 0;

const DEFAULT_DURATION = 3500;

function push(type, message, duration = DEFAULT_DURATION) {
    const id = ++nextId;
    toasts.push({ id, type, message });
    if (duration > 0) {
        setTimeout(() => remove(id), duration);
    }
}

function remove(id) {
    const index = toasts.findIndex((toast) => toast.id === id);
    if (index !== -1) {
        toasts.splice(index, 1);
    }
}

export function useToast() {
    return {
        toasts,
        success: (message, duration) => push('success', message, duration),
        error: (message, duration) => push('error', message, duration),
        info: (message, duration) => push('info', message, duration),
        remove,
    };
}