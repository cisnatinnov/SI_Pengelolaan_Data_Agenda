<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import KegiatanForm from '../components/KegiatanForm.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const user = window.Laravel?.user ?? null;
const canManage = user?.role_slug === 'staff';
const isOpd = user?.role_slug === 'opd';
const toast = useToast();
const { confirm } = useConfirm();

const items = ref([]);
const loading = ref(false);
const error = ref('');
const confirmingId = ref(null);
const kehadiranListId = ref(null);

const showForm = ref(false);
const editingItem = ref(null);

const realisasiLabels = {
    terlaksana: { text: 'Terlaksana', class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' },
    tidak: { text: 'Tidak Terlaksana', class: 'bg-red-500/15 text-red-700 dark:text-red-300' },
};

const statusLabels = {
    pelaksanaan: { text: 'Pelaksanaan', class: 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300' },
    laporan: { text: 'Laporan', class: 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300' },
};

const formatTanggal = (value) => {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const fetchItems = async () => {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get('/api/kegiatan');
        items.value = data;
    } catch (err) {
        error.value = 'Gagal memuat data kegiatan.';
    } finally {
        loading.value = false;
    }
};

const openCreate = () => {
    editingItem.value = null;
    showForm.value = true;
};

const openEdit = (item) => {
    editingItem.value = item;
    showForm.value = true;
};

const handleSubmit = async (payload) => {
    try {
        if (editingItem.value) {
            await axios.put(`/api/kegiatan/${editingItem.value.id}`, payload);
            toast.success('Kegiatan berhasil diperbarui.');
        } else {
            await axios.post('/api/kegiatan', payload);
            toast.success('Kegiatan berhasil ditambahkan.');
        }
        showForm.value = false;
        await fetchItems();
    } catch (err) {
        const message =
            err.response?.data?.errors?.tanggal_kegiatan?.[0] ??
            err.response?.data?.message ??
            'Gagal menyimpan data kegiatan.';
        toast.error(message);
    }
};

const removeItem = async (item) => {
    const ok = await confirm({
        title: 'Hapus Kegiatan',
        message: `Hapus kegiatan "${item.nama_kegiatan}"?`,
        confirmText: 'Hapus',
        danger: true,
    });
    if (!ok) return;
    try {
        await axios.delete(`/api/kegiatan/${item.id}`);
        toast.success('Kegiatan berhasil dihapus.');
        await fetchItems();
    } catch (err) {
        toast.error('Gagal menghapus kegiatan.');
    }
};

const confirmAttendance = async (item, status) => {
    if (confirmingId.value) return;
    confirmingId.value = item.id;
    try {
        await axios.post(`/api/kegiatan/${item.id}/kehadiran`, { status });
        toast.success(`Kehadiran berhasil dikonfirmasi sebagai ${status === 'hadir' ? 'hadir' : 'tidak hadir'}.`);
        await fetchItems();
    } catch (err) {
        toast.error('Gagal mengonfirmasi kehadiran.');
    } finally {
        confirmingId.value = null;
    }
};

const ownKehadiran = (item) =>
    item.kehadiran?.find((k) => k.user_id === user?.id)?.status ?? null;

const toggleKehadiranList = (id) => {
    kehadiranListId.value = kehadiranListId.value === id ? null : id;
};

onMounted(fetchItems);
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-display font-bold gradient-brand-text">Kegiatan</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ canManage ? 'Kelola data kegiatan' : 'Data kegiatan (hanya baca)' }}
                </p>
            </div>
            <button
                v-if="canManage"
                @click="openCreate"
                class="px-4 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
            >
                + Tambah Kegiatan
            </button>
        </div>

        <div
            v-if="error"
            class="mb-4 p-4 bg-red-50/80 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 text-sm rounded-xl backdrop-blur"
        >
            {{ error }}
        </div>

        <div class="glass rounded-2xl border border-slate-200/70 dark:border-white/10 overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                Memuat data...
            </div>

            <div
                v-else-if="items.length === 0"
                class="p-8 text-center text-sm text-slate-500 dark:text-slate-400"
            >
                Belum ada data kegiatan.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10">
                    <thead class="bg-slate-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Kegiatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tempat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Uraian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Realisasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kehadiran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Penyusun</th>
                            <th v-if="canManage" class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-white/10">
                        <tr
                            v-for="(item, index) in items"
                            :key="item.id"
                            class="hover:bg-slate-50/60 dark:hover:bg-white/5 transition-colors"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                {{ index + 1 }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">{{ item.nama_kegiatan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tempat_kegiatan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatTanggal(item.tanggal_kegiatan) }}</td>
                            <td class="px-6 py-4 text-sm max-w-xs">
                                <p class="truncate" :title="item.uraian_kegiatan">{{ item.uraian_kegiatan }}</p>
                                <p v-if="item.keterangan" class="mt-0.5 text-xs text-slate-400 dark:text-slate-500 truncate" :title="item.keterangan">
                                    {{ item.keterangan }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="realisasiLabels[item.realisasi_pelaksanaan]?.class ?? 'bg-slate-500/10 text-slate-600 dark:text-slate-300'"
                                >
                                    {{ realisasiLabels[item.realisasi_pelaksanaan]?.text ?? item.realisasi_pelaksanaan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="statusLabels[item.status]?.class ?? 'bg-slate-500/10 text-slate-600 dark:text-slate-300'"
                                >
                                    {{ statusLabels[item.status]?.text ?? item.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <template v-if="isOpd">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="confirmAttendance(item, 'hadir')"
                                            :disabled="confirmingId === item.id"
                                            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-colors disabled:opacity-50"
                                            :class="ownKehadiran(item) === 'hadir'
                                                ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40'
                                                : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 border-transparent hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-300'"
                                        >
                                            Hadir
                                        </button>
                                        <button
                                            @click="confirmAttendance(item, 'tidak')"
                                            :disabled="confirmingId === item.id"
                                            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-colors disabled:opacity-50"
                                            :class="ownKehadiran(item) === 'tidak'
                                                ? 'bg-red-500/15 text-red-700 dark:text-red-300 border-red-500/40'
                                                : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 border-transparent hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-300'"
                                        >
                                            Tidak Hadir
                                        </button>
                                    </div>
                                </template>
                                <span v-else class="text-sm text-slate-500 dark:text-slate-400">
                                    <span>{{ item.hadir_count }} hadir · {{ item.tidak_count }} tidak</span>
                                    <button
                                        v-if="item.kehadiran?.length"
                                        @click="toggleKehadiranList(item.id)"
                                        class="ml-2 text-xs font-medium text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
                                    >
                                        {{ kehadiranListId === item.id ? 'Sembunyikan' : 'Daftar OPD' }}
                                    </button>
                                    <ul v-if="kehadiranListId === item.id" class="mt-2 space-y-1">
                                        <li
                                            v-for="k in item.kehadiran"
                                            :key="k.id"
                                            class="flex items-center justify-between gap-3 text-xs"
                                        >
                                            <span class="text-slate-600 dark:text-slate-300">{{ k.user?.name ?? 'OPD' }}</span>
                                            <span
                                                class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium"
                                                :class="k.status === 'hadir'
                                                    ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'
                                                    : 'bg-red-500/15 text-red-700 dark:text-red-300'"
                                            >
                                                {{ k.status === 'hadir' ? 'Hadir' : 'Tidak Hadir' }}
                                            </span>
                                        </li>
                                    </ul>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ item.nama_penyusun ?? '-' }}
                            </td>
                            <td v-if="canManage" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button
                                    @click="openEdit(item)"
                                    class="text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 mr-3"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="removeItem(item)"
                                    class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <KegiatanForm
            v-if="showForm"
            :item="editingItem"
            @submit="handleSubmit"
            @close="showForm = false"
        />
    </div>
</template>