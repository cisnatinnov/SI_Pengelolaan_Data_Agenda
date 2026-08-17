<script setup>
import { computed } from 'vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
    active: { type: String, default: 'dashboard' },
    mobile: { type: Boolean, default: false },
    role: { type: String, default: 'staff' },
});

const emit = defineEmits(['navigate', 'close']);

const allLinks = [
    { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', roles: ['admin', 'staff', 'asisten_daerah', 'opd'] },
    { key: 'kegiatan', label: 'Kegiatan', icon: 'kegiatan', roles: ['staff', 'asisten_daerah', 'opd'] },
    { key: 'surat', label: 'Surat', icon: 'surat', roles: ['staff'] },
    { key: 'disposisi', label: 'Disposisi', icon: 'disposisi', roles: ['staff', 'asisten_daerah'] },
    { key: 'pengingat', label: 'Pengingat', icon: 'pengingat', roles: ['staff', 'asisten_daerah', 'opd'] },
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
    'disposisi': `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <path d="M7 3h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
            <path d="M14 3v5h5"/>
            <path d="M9 13h6M9 17h6"/>
        </svg>`,
    'surat': `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <path d="M4 4h16v16H4z" rx="2"/>
            <path d="M8 9h8M8 13h8M8 17h5" stroke-linecap="round"/>
        </svg>`,
    'pengingat': `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            class="w-5 h-5 shrink-0">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 3" stroke-linecap="round"/>
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
        class="hidden lg:flex flex-col shrink-0 transition-all duration-300 ease-out border-r border-slate-200/70  bg-white/50  backdrop-blur-xl"
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
                        : 'text-slate-600  hover:bg-slate-100 ',
                    collapsed ? 'justify-center px-0' : '',
                ]"
                @click="emit('navigate', link.key)"
            >
                <span v-html="icons[link.icon]" />
                <span v-if="!collapsed" class="whitespace-nowrap">{{ link.label }}</span>
            </button>
        </nav>

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
            class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col glass border-r border-slate-200/70 lg:hidden"
        >
            <div class="flex items-center justify-between px-5 h-16 border-b border-slate-200/70">
                <h2 class="font-display font-bold gradient-brand-text">DATA AGENDA</h2>
                <button
                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-200/60 transition-colors"
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
                            : 'text-slate-600 hover:bg-slate-100'
                    "
                    @click="emit('navigate', link.key)"
                >
                    <span v-html="icons[link.icon]" />
                    <span>{{ link.label }}</span>
                </button>
            </nav>

        </aside>
    </transition>
</template>