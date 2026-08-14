import { reactive } from 'vue';

const state = reactive({
    visible: false,
    title: 'Konfirmasi',
    message: '',
    confirmText: 'Ya',
    cancelText: 'Batal',
    danger: false,
    resolve: null,
});

/**
 * Promise-based confirmation dialog. Returns a Promise<boolean> that
 * resolves `true` when the user confirms, or `false` when cancelled.
 *
 * `confirm(options)` accepts a plain message string or an options object:
 * { title, message, confirmText, cancelText, danger }.
 */
function confirm(options) {
    const opts = typeof options === 'string' ? { message: options } : options;
    state.title = opts.title ?? 'Konfirmasi';
    state.message = opts.message ?? '';
    state.confirmText = opts.confirmText ?? 'Ya';
    state.cancelText = opts.cancelText ?? 'Batal';
    state.danger = opts.danger ?? false;
    state.visible = true;
    return new Promise((resolve) => {
        state.resolve = resolve;
    });
}

function settle(result) {
    state.visible = false;
    if (state.resolve) {
        state.resolve(result);
        state.resolve = null;
    }
}

export function useConfirm() {
    return {
        state,
        confirm,
        confirmAction: () => settle(true),
        cancelAction: () => settle(false),
    };
}