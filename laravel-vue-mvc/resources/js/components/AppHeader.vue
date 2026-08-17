<script setup>
import NotificationBell from './NotificationBell.vue';

defineProps({
    active: { type: String, default: 'dashboard' },
    sidebarCollapsed: { type: Boolean, default: false },
    user: { type: Object, default: null },
});

defineEmits(['navigate', 'toggle-sidebar', 'logout']);
</script>

<template>
    <header
        class="sticky top-0 z-40 glass border-b border-slate-200/70 ">
        <div class="h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button
                    class="p-2 rounded-lg text-slate-600  hover:bg-slate-200/60  transition-colors lg:hidden"
                    @click="$emit('toggle-sidebar')"
                    aria-label="Toggle sidebar"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                    </svg>
                </button>

                <button
                    class="p-2 rounded-lg text-slate-600  hover:bg-slate-200/60  transition-colors hidden lg:inline-flex"
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
                    <p class="text-xs text-slate-500  truncate hidden sm:block">
                        Sistem Pengelolaan Data Agenda
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span v-if="user" class="hidden sm:block text-sm text-slate-600 ">
                    {{ user.name }}
                </span>
                <NotificationBell
                    v-if="user && user.role_slug !== 'admin'"
                    :user="user"
                    @navigate="$emit('navigate', $event)"
                />
                <button
                    class="p-2 rounded-lg text-slate-600  hover:bg-red-100/60  hover:text-red-600  transition-colors"
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