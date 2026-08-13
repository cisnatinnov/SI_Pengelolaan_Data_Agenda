<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import SuratKegiatanForm from '../components/SuratKegiatanForm.vue';

const props = defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['navigate']);

const items = ref([]);
const loading = ref(false);
const error = ref('');

const showForm = ref(false);
const editingItem = ref(null);
const autoOpenConsumed = ref(false);

const keteranganLabels = {
    diterima: { text: 'Diterima', class: 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300' },
    ditolak: { text: 'Ditolak', class: 'bg-red-500/15 text-red-700 dark:text-red-300' },
    disahkan: { text: 'Disahkan', class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' },
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
        const params = props.payload?.surat_undangan_id
            ? { surat_undangan_id: props.payload.surat_undangan_id }
            : {};
        const { data } = await axios.get('/api/surat-kegiatan', { params });
        items.value = data;

        if (props.payload?.openForm && !autoOpenConsumed.value && items.value.length > 0) {
            autoOpenConsumed.value = true;
            openEdit(items.value[0]);
        }
    } catch (err) {
        error.value = 'Gagal memuat data disposisi.';
    } finally {
        loading.value = false;
    }
};

const openEdit = (item) => {
    editingItem.value = item;
    showForm.value = true;
};

const handleSubmit = async (payload) => {
    try {
        await axios.put(`/api/surat-kegiatan/${editingItem.value.id}`, payload);
        showForm.value = false;
        await fetchItems();
    } catch (err) {
        error.value = 'Gagal menyimpan data disposisi.';
    }
};

const removeItem = async (item) => {
    if (!confirm(`Hapus disposisi "${item.nomor_surat}"?`)) return;
    try {
        await axios.delete(`/api/surat-kegiatan/${item.id}`);
        await fetchItems();
    } catch (err) {
        error.value = 'Gagal menghapus disposisi.';
    }
};

watch(
    () => props.payload,
    () => {
        autoOpenConsumed.value = false;
        fetchItems();
    }
);

onMounted(fetchItems);
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-display font-bold gradient-brand-text">Disposisi</h2>
                    <button
                        v-if="payload?.surat_undangan_id"
                        class="px-3 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 rounded-full hover:bg-indigo-500/20 transition-colors"
                        @click="emit('navigate', 'surat-kegiatan')"
                    >
                        Lihat Semua
                    </button>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Data otomatis dibuat dari Surat
                </p>
            </div>
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
                Belum ada data disposisi.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10">
                    <thead class="bg-slate-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nomor Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Asal Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Perihal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kepada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Penerima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dituju</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatTanggal(item.tanggal) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.nomor_surat }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.asal_surat }}</td>
                            <td class="px-6 py-4 text-sm">{{ item.perihal }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.kepada }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tandatangan_penerima ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tandatangan_dituju ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="keteranganLabels[item.keterangan]?.class ?? 'bg-slate-500/10 text-slate-600 dark:text-slate-300'"
                                >
                                    {{ keteranganLabels[item.keterangan]?.text ?? item.keterangan }}
                                </span>
                                <p
                                    v-if="item.keterangan === 'ditolak' && item.alasan"
                                    class="mt-1 text-xs text-red-600 dark:text-red-400"
                                >
                                    {{ item.alasan }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
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

        <SuratKegiatanForm
            v-if="showForm"
            :item="editingItem"
            @submit="handleSubmit"
            @close="showForm = false"
        />
    </div>
</template>