<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import KegiatanForm from '../components/KegiatanForm.vue';

defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const user = window.Laravel?.user ?? null;
const canManage = user?.role === 'staff';

const items = ref([]);
const loading = ref(false);
const error = ref('');

const showForm = ref(false);
const editingItem = ref(null);

const realisasiLabels = {
    terlaksana: { text: 'Terlaksana', class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' },
    tidak: { text: 'Tidak', class: 'bg-red-500/15 text-red-700 dark:text-red-300' },
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
        } else {
            await axios.post('/api/kegiatan', payload);
        }
        showForm.value = false;
        await fetchItems();
    } catch (err) {
        error.value = 'Gagal menyimpan data kegiatan.';
    }
};

const removeItem = async (item) => {
    if (!confirm(`Hapus kegiatan "${item.nama_kegiatan}"?`)) return;
    try {
        await axios.delete(`/api/kegiatan/${item.id}`);
        await fetchItems();
    } catch (err) {
        error.value = 'Gagal menghapus kegiatan.';
    }
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <template v-if="item.status === 'laporan'">
                                    <p>{{ item.nama_penyusun ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">TTD: {{ item.tanda_tangan_penyusun ?? '-' }}</p>
                                </template>
                                <template v-else>-</template>
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