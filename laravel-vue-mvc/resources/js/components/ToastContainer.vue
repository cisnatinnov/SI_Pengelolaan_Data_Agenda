<script setup>
import { useToast } from '../composables/useToast';

const { toasts, remove } = useToast();

const styles = {
    success: {
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        classes:
            'border-emerald-500/40 text-emerald-700 ',
        iconClasses: 'text-emerald-500',
    },
    error: {
        icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        classes: 'border-red-500/40 text-red-700 ',
        iconClasses: 'text-red-500',
    },
    info: {
        icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        classes: 'border-cyan-500/40 text-cyan-700 ',
        iconClasses: 'text-cyan-500',
    },
};
</script>

<template>
    <div
        class="fixed top-4 right-4 z-[60] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm pointer-events-none"
    >
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex items-start gap-3 p-4 rounded-xl shadow-xl glass  border backdrop-blur-xl animate-toast-in"
                :class="styles[toast.type]?.classes"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="w-5 h-5 shrink-0 mt-0.5"
                    :class="styles[toast.type]?.iconClasses"
                >
                    <path
                        :d="styles[toast.type]?.icon"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <p class="flex-1 text-sm font-medium leading-snug">{{ toast.message }}</p>
                <button
                    type="button"
                    class="shrink-0 text-slate-400 hover:text-slate-600  transition-colors"
                    :aria-label="'Tutup notifikasi'"
                    @click="remove(toast.id)"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="w-4 h-4"
                    >
                        <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.animate-toast-in {
    animation: toast-in 0.25s ease;
}

@keyframes toast-in {
    from {
        opacity: 0;
        transform: translateY(-8px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.toast-enter-active,
.toast-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(12px);
}
</style>