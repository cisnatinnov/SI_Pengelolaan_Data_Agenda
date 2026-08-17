<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['navigate']);

const container = ref(null);
const open = ref(false);
const loading = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const toast = useToast();

let channel = null;

const sourceLabels = {
    surat: { text: 'Surat', class: 'bg-cyan-500/15 text-cyan-700 ' },
    kegiatan: { text: 'Kegiatan', class: 'bg-indigo-500/15 text-indigo-700 ' },
    disposisi: { text: 'Disposisi', class: 'bg-emerald-500/15 text-emerald-700 ' },
};

const formatTanggal = (value) => {
    if (!value) return '';
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const relativeTime = (value) => {
    if (!value) return '';
    const diff = Date.now() - new Date(value).getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1) return 'Baru saja';
    if (minutes < 60) return `${minutes} menit lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} hari lalu`;
    return formatTanggal(value);
};

const fetchNotifications = async () => {
    try {
        const { data } = await axios.get('/api/pengingat/notifications');
        notifications.value = data.notifications ?? [];
        unreadCount.value = data.unread_count ?? 0;
    } catch (err) {
        // 401/419 redirects are handled by the axios interceptor.
    }
};

const toggleOpen = () => {
    open.value = !open.value;
    if (open.value) fetchNotifications();
};

const markAsRead = async (item) => {
    if (item.read_at) return;
    try {
        await axios.post(`/api/pengingat/${item.id}/read`);
        item.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (err) {
        // Ignore transient failures; next resync catches up.
    }
};

const markAllAsRead = async () => {
    loading.value = true;
    try {
        await axios.post('/api/pengingat/read-all');
        notifications.value.forEach((n) => (n.read_at = new Date().toISOString()));
        unreadCount.value = 0;
        toast.success('Semua notifikasi telah dibaca.');
    } catch (err) {
        toast.error('Gagal menandai notifikasi.');
    } finally {
        loading.value = false;
    }
};

const openPengingat = (item) => {
    markAsRead(item);
    open.value = false;
    emit('navigate', 'pengingat');
};

const onDocumentClick = (event) => {
    if (container.value && !container.value.contains(event.target)) {
        open.value = false;
    }
};

const onVisibilityChange = () => {
    if (!document.hidden) fetchNotifications();
};

const subscribeToRealtime = () => {
    if (!props.user?.id || !window.Echo) return;
    channel = window.Echo.private(`App.Models.User.${props.user.id}`);
    channel.listen('.pengingat.notification', fetchNotifications);
};

onMounted(() => {
    fetchNotifications();
    subscribeToRealtime();
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('visibilitychange', onVisibilityChange);
});

onUnmounted(() => {
    channel?.stopListening('.pengingat.notification');
    window.Echo?.leaveChannel(`App.Models.User.${props.user?.id}`);
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>

<template>
    <div ref="container" class="relative">
        <button
            class="relative p-2 rounded-lg text-slate-600  hover:bg-slate-200/60  transition-colors"
            @click.stop="toggleOpen"
            aria-label="Notifikasi pengingat"
            :title="'Notifikasi (' + unreadCount + ' belum dibaca)'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5">
                <path
                    d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center shadow-lg shadow-red-500/30"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <transition name="fade">
            <div
                v-if="open"
                class="absolute right-0 mt-2 w-80 sm:w-96 glass  rounded-2xl shadow-2xl border border-slate-200/70  overflow-hidden z-50"
                @click.stop
            >
                <div class="px-4 py-3 border-b border-slate-200  flex items-center justify-between">
                    <h3 class="text-sm font-display font-bold gradient-brand-text">Notifikasi Pengingat</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        :disabled="loading"
                        class="text-xs font-medium text-indigo-500  hover:text-indigo-700  disabled:opacity-50"
                    >
                        {{ loading ? 'Memproses...' : 'Tandai semua dibaca' }}
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <div
                        v-if="notifications.length === 0"
                        class="p-8 text-center text-sm text-slate-500 "
                    >
                        Belum ada notifikasi.
                    </div>

                    <button
                        v-for="item in notifications"
                        :key="item.id"
                        class="w-full text-left px-4 py-3 border-b border-slate-200/60  hover:bg-slate-50/80  transition-colors"
                        @click="openPengingat(item)"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ item.judul }}</p>
                                <p v-if="item.deskripsi" class="mt-0.5 text-xs text-slate-500  line-clamp-2">
                                    {{ item.deskripsi }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-400 ">
                                    {{ relativeTime(item.created_at) }}
                                </p>
                            </div>
                            <span
                                v-if="!item.read_at"
                                class="mt-1 w-2 h-2 rounded-full bg-red-500 shrink-0"
                            ></span>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium"
                                :class="sourceLabels[item.source]?.class ?? 'bg-slate-500/10 text-slate-600 '"
                            >
                                {{ sourceLabels[item.source]?.text ?? item.source }}
                            </span>
                            <span class="text-[11px] text-slate-400 ">
                                {{ formatTanggal(item.tanggal_pengingat) }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>