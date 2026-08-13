<script setup>
import { ref, computed } from 'vue';
import AppHeader from './components/AppHeader.vue';
import AppFooter from './components/AppFooter.vue';
import AppSidebar from './components/AppSidebar.vue';
import Dashboard from './views/Dashboard.vue';
import Disposisi from './views/Disposisi.vue';
import Surat from './views/Surat.vue';
import Kegiatan from './views/Kegiatan.vue';
import Pengguna from './views/Pengguna.vue';
import Pengingat from './views/Pengingat.vue';

const user = window.Laravel?.user ?? null;

const logout = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';

    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    form.appendChild(token);

    document.body.appendChild(form);
    form.submit();
};

const activeView = ref('dashboard');
const viewPayload = ref(null);
const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const views = {
    dashboard: Dashboard,
    disposisi: Disposisi,
    surat: Surat,
    kegiatan: Kegiatan,
    pengguna: Pengguna,
    pengingat: Pengingat,
};

const navigate = (key, payload = null) => {
    activeView.value = key;
    viewPayload.value = payload;
    mobileSidebarOpen.value = false;
};

// Roles allowed per view; must stay in sync with AppSidebar.
const viewRoles = {
    dashboard: ['admin', 'staff', 'asisten_daerah', 'opd'],
    kegiatan: ['staff', 'asisten_daerah', 'opd'],
    surat: ['staff'],
    disposisi: ['staff', 'asisten_daerah'],
    pengguna: ['admin'],
    pengingat: ['staff', 'asisten_daerah', 'opd'],
};

const currentView = computed(() =>
    viewRoles[activeView.value]?.includes(user?.role_slug) ? activeView.value : 'dashboard'
);

const toggleSidebar = () => {
    if (window.innerWidth < 1024) {
        mobileSidebarOpen.value = !mobileSidebarOpen.value;
    } else {
        sidebarCollapsed.value = !sidebarCollapsed.value;
    }
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 bg-grid bg-grid-faded anim-grid-pan opacity-60 dark:opacity-40"></div>
            <div
                class="absolute -top-32 -left-32 w-[36rem] h-[36rem] rounded-full bg-cyan-400/20 blur-3xl dark:bg-cyan-500/10"
            ></div>
            <div
                class="absolute top-1/3 -right-40 w-[32rem] h-[32rem] rounded-full bg-fuchsia-400/15 blur-3xl dark:bg-fuchsia-600/10"
            ></div>
            <div
                class="absolute bottom-0 left-1/3 w-[28rem] h-[28rem] rounded-full bg-indigo-400/15 blur-3xl dark:bg-indigo-600/10"
            ></div>
        </div>

        <AppHeader
            :active="activeView"
            :sidebar-collapsed="sidebarCollapsed"
            :user="user"
            @navigate="navigate"
            @toggle-sidebar="toggleSidebar"
            @logout="logout"
        />

        <div class="flex flex-1 relative">
            <AppSidebar
                :collapsed="sidebarCollapsed"
                :active="activeView"
                :role="user?.role_slug ?? 'staff'"
                @navigate="navigate"
            />

            <AppSidebar
                v-if="mobileSidebarOpen"
                :collapsed="false"
                :active="activeView"
                :role="user?.role_slug ?? 'staff'"
                mobile
                @navigate="navigate"
                @close="mobileSidebarOpen = false"
            />

            <main class="flex-1 min-w-0">
                <div class="px-4 sm:px-6 lg:px-8 py-6 lg:py-8 max-w-[1400px] mx-auto w-full">
                    <component :is="views[currentView]" :payload="viewPayload" @navigate="navigate" />

                    <div class="mt-8">
                        <AppFooter />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>