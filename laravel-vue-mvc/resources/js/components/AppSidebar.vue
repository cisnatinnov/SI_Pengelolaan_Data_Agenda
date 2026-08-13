<script setup>
import { computed } from 'vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
    active: { type: String, default: 'dashboard' },
    mobile: { type: Boolean, default: false },
    theme: { type: String, default: 'light' },
    role: { type: String, default: 'staff' },
});

const emit = defineEmits(['navigate', 'close', 'toggle-theme']);

const allLinks = [
    { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', roles: ['admin', 'staff', 'asisten_daerah', 'opd'] },
    { key: 'kegiatan', label: 'Kegiatan', icon: 'kegiatan', roles: ['admin', 'staff', 'asisten_daerah', 'opd'] },
    { key: 'surat-undangan', label: 'Surat', icon: 'surat-undangan', roles: ['staff'] },
    { key: 'surat-kegiatan', label: 'Disposisi', icon: 'surat-kegiatan', roles: ['staff'] },
    { key: 'pengguna', label: 'Pengguna', icon: 'pengguna', roles: ['admin'] },
];

const navLinks = computed(() => allLinks.filter((link) => link.roles.includes(props.role)));

const icons = {
    dashboard: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <rect x="3" y="3" width="7" height="9" rx="1.5"/>
            <rect x="14" y="3" width="7" height="5" rx="1.5"/>
            <rect x="14" y="12" width="7" height="9" rx="1.5"/>
            <rect x="3" y="16" width="7" height="5" rx="1.5"/>
        </svg>`,
    kegiatan: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <rect x="3" y="5" width="18" height="16" rx="2"/>
            <path d="M8 3v4M16 3v4M3 10h18" stroke-linecap="round"/>
        </svg>`,
    'surat-kegiatan': `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
            <path d="M14 3v5h5"/>
            <path d="M9 13h6M9 17h6"/>
        </svg>`,
    'surat-undangan': `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <path d="M4 4h16v16H4z" rx="2"/>
            <path d="M8 9h8M8 13h8M8 17h5" stroke-linecap="round"/>
        </svg>`,
    pengguna: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <circle cx="9" cy="8" r="3.5"/>
            <path d="M3.5 20a5.5 5.5 0 0 1 11 0" stroke-linecap="round"/>
            <circle cx="17" cy="9" r="2.5"/>
            <path d="M15.5 15.5a4.5 4.5 0 0 1 5 4.5" stroke-linecap="round"/>
        </svg>`,
};
</script>

<template>
    <aside
        v-if="!mobile"
        class="hidden lg:flex flex-col shrink-0 transition-all duration-300 ease-out border-r border-slate-200/70 dark:border-white/10 bg-white/50 dark:bg-slate-900/40 backdrop-blur-xl"
        :class="collapsed ? 'w-[76px]' : 'w-64'"
    >
        <nav class="flex-1 py-6 px-3 space-y-2 overflow-y-auto">
            <button
                v-for="link in navLinks"
                :key="link.key"
                :title="collapsed ? link.label : undefined"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left text-sm font-medium transition-all"
                :class="[
                    active === link.key
                        ? 'gradient-brand text-white shadow-lg shadow-indigo-500/25'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5',
                    collapsed ? 'justify-center px-0' : '',
                ]"
                @click="emit('navigate', link.key)"
            >
                <span v-html="icons[link.icon]" />
                <span v-if="!collapsed" class="whitespace-nowrap">{{ link.label }}</span>
            </button>
        </nav>

        <div class="p-3 pb-5 border-t border-slate-200/70 dark:border-white/10">
            <button
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="
                    collapsed
                        ? 'justify-center px-0 text-slate-500 dark:text-slate-400 hover:text-indigo-500 dark:hover:text-indigo-400'
                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5'
                "
                :title="theme === 'dark' ? 'Pindah ke mode terang' : 'Pindah ke mode gelap'"
                @click="emit('toggle-theme')"
            >
                <svg v-if="theme === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 shrink-0">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32 1.41-1.41" stroke-linecap="round" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 shrink-0">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" stroke-linejoin="round" />
                </svg>
                <template v-if="!collapsed">
                    <span class="whitespace-nowrap">{{ theme === 'dark' ? 'Mode Gelap' : 'Mode Terang' }}</span>
                    <span
                        class="ml-auto relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                        :class="theme === 'dark' ? 'bg-indigo-600' : 'bg-slate-300'"
                    >
                        <span
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                            :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </span>
                </template>
            </button>
        </div>
    </aside>

    <transition name="fade">
        <div
            v-if="mobile"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
            @click="emit('close')"
        ></div>
    </transition>

    <transition name="slide-right">
        <aside
            v-if="mobile"
            class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col glass-dark lg:hidden"
        >
            <div class="flex items-center justify-between px-5 h-16 border-b border-white/10">
                <h2 class="font-display font-bold gradient-brand-text">DATA AGENDA</h2>
                <button
                    class="p-2 rounded-lg text-slate-300 hover:bg-white/10 transition-colors"
                    @click="emit('close')"
                    aria-label="Close sidebar"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                        <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-2 overflow-y-auto">
                <button
                    v-for="link in navLinks"
                    :key="link.key"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left text-sm font-medium transition-all"
                    :class="
                        active === link.key
                            ? 'gradient-brand text-white shadow-lg shadow-indigo-500/25'
                            : 'text-slate-300 hover:bg-white/5'
                    "
                    @click="emit('navigate', link.key)"
                >
                    <span v-html="icons[link.icon]" />
                    <span>{{ link.label }}</span>
                </button>
            </nav>

            <div class="p-3 pb-6 border-t border-white/10">
                <button
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:bg-white/5 transition-all"
                    @click="emit('toggle-theme')"
                >
                    <svg v-if="theme === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 shrink-0">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32 1.41-1.41" stroke-linecap="round" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 shrink-0">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" stroke-linejoin="round" />
                    </svg>
                    <span>{{ theme === 'dark' ? 'Mode Gelap' : 'Mode Terang' }}</span>
                    <span
                        class="ml-auto relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                        :class="theme === 'dark' ? 'bg-indigo-600' : 'bg-slate-500/40'"
                    >
                        <span
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                            :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </span>
                </button>
            </div>
        </aside>
    </transition>
</template>