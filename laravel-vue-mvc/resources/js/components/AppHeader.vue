<script setup>
defineProps({
    active: { type: String, default: 'dashboard' },
    theme: { type: String, default: 'light' },
    sidebarCollapsed: { type: Boolean, default: false },
    user: { type: Object, default: null },
});

defineEmits(['navigate', 'toggle-sidebar', 'toggle-theme', 'logout']);
</script>

<template>
    <header
        class="sticky top-0 z-40 glass border-b border-slate-200/70 dark:border-white/10">
        <div class="h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button
                    class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors lg:hidden"
                    @click="$emit('toggle-sidebar')"
                    aria-label="Toggle sidebar"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                    </svg>
                </button>

                <button
                    class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors hidden lg:inline-flex"
                    @click="$emit('toggle-sidebar')"
                    aria-label="Collapse sidebar"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="w-5 h-5"
                        :class="sidebarCollapsed ? 'rotate-180 transition-transform' : 'transition-transform'"
                    >
                        <rect x="3" y="4" width="18" height="16" rx="2" />
                        <path d="M9 4v16" />
                        <path d="M10.5 9l-2 3 2 3" />
                    </svg>
                </button>

                <div class="min-w-0">
                    <h1 class="text-lg font-display font-bold gradient-brand-text truncate">DATA AGENDA</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate hidden sm:block">
                        Sistem Pengelolaan Data Agenda
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span v-if="user" class="hidden sm:block text-sm text-slate-600 dark:text-slate-300">
                    {{ user.name }}
                </span>
                <button
                    class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors"
                    @click="$emit('toggle-theme')"
                    :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                >
                    <svg v-if="theme === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32 1.41-1.41" stroke-linecap="round" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" stroke-linejoin="round" />
                    </svg>
                </button>
                <button
                    class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-red-100/60 dark:hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                    @click="$emit('logout')"
                    aria-label="Logout"
                    title="Logout"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </header>
</template>