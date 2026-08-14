<script setup>
import { useConfirm } from '../composables/useConfirm';

const { state, confirmAction, cancelAction } = useConfirm();
</script>

<template>
    <div
        v-if="state.visible"
        class="fixed inset-0 z-[55] flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
        @click.self="cancelAction"
    >
        <div class="w-full max-w-sm glass dark:glass-dark rounded-2xl shadow-2xl animate-pop-in">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-white/10 flex items-center gap-3">
                <span
                    v-if="state.danger"
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-red-500/15 text-red-600 dark:text-red-400 shrink-0"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="w-5 h-5"
                    >
                        <path d="M12 9v4m0 4h.01M10.29 3.86l-8.29 14.04A2 2 0 0 0 3.73 21h16.54a2 2 0 0 0 1.73-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <h3 class="text-lg font-display font-bold gradient-brand-text">{{ state.title }}</h3>
            </div>

            <div class="px-6 py-4">
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ state.message }}</p>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-white/10 flex justify-end gap-3">
                <button
                    type="button"
                    @click="cancelAction"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-white/10 rounded-xl hover:bg-slate-200 dark:hover:bg-white/15 transition-colors"
                >
                    {{ state.cancelText }}
                </button>
                <button
                    type="button"
                    @click="confirmAction"
                    class="px-4 py-2 text-sm font-medium text-white rounded-xl shadow-lg hover:opacity-90 transition-opacity"
                    :class="state.danger
                        ? 'bg-gradient-to-r from-red-500 to-rose-600 shadow-red-500/25'
                        : 'gradient-brand shadow-indigo-500/25'"
                >
                    {{ state.confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-pop-in {
    animation: pop-in 0.2s ease;
}

@keyframes pop-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(6px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>