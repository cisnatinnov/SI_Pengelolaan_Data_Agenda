<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SuratForm from '../components/SuratForm.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

const emit = defineEmits(['navigate']);
const toast = useToast();
const { confirm } = useConfirm();

defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const items = ref([]);
const loading = ref(false);
const error = ref('');

const showForm = ref(false);
const editingItem = ref(null);

const openDisposisi = (item) => {
    emit('navigate', 'disposisi', { surat_id: item.id });
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
        const { data } = await axios.get('/api/surat');
        items.value = data;
    } catch (err) {
        error.value = 'Gagal memuat data surat.';
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
            await axios.put(`/api/surat/${editingItem.value.id}`, payload);
            showForm.value = false;
            toast.success('Surat berhasil diperbarui.');
            await fetchItems();
        } else {
            const { data } = await axios.post('/api/surat', payload);
            showForm.value = false;
            toast.success('Surat berhasil ditambahkan.');
            emit('navigate', 'disposisi', {
                surat_id: data.id,
            });
        }
    } catch (err) {
        toast.error('Gagal menyimpan data surat.');
    }
};

const removeItem = async (item) => {
    const ok = await confirm({
        title: 'Hapus Surat',
        message: `Hapus surat "${item.nomor_surat}"?`,
        confirmText: 'Hapus',
        danger: true,
    });
    if (!ok) return;
    try {
        await axios.delete(`/api/surat/${item.id}`);
        toast.success('Surat berhasil dihapus.');
        await fetchItems();
    } catch (err) {
        toast.error('Gagal menghapus surat.');
    }
};

onMounted(fetchItems);
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-display font-bold gradient-brand-text">Surat</h2>
                <p class="mt-1 text-sm text-slate-500 ">Kelola data surat</p>
            </div>
            <button
                @click="openCreate"
                class="px-4 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
            >
                + Tambah Surat
            </button>
        </div>

        <div
            v-if="error"
            class="mb-4 p-4 bg-red-50/80  border border-red-200  text-red-700  text-sm rounded-xl backdrop-blur"
        >
            {{ error }}
        </div>

        <div class="glass rounded-2xl border border-slate-200/70  overflow-hidden">
            <div v-if="loading" class="p-8 text-center text-sm text-slate-500 ">
                Memuat data...
            </div>

            <div
                v-else-if="items.length === 0"
                class="p-8 text-center text-sm text-slate-500 "
            >
                Belum ada data surat.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 ">
                    <thead class="bg-slate-50/70 ">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Nomor Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Asal Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Perihal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Kepada</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Tgl. Pelaksanaan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Tempat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Pembawa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">TTD</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500  uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 ">
                        <tr
                            v-for="(item, index) in items"
                            :key="item.id"
                            class="hover:bg-slate-50/60  transition-colors"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 ">
                                {{ index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatTanggal(item.tanggal) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.nomor_surat }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.asal_surat }}</td>
                            <td class="px-6 py-4 text-sm">{{ item.perihal }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.kepada }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatTanggal(item.tanggal_pelaksanaan) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tempat_pelaksanaan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.pembawa_surat }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tandatangan ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button
                                    @click="openDisposisi(item)"
                                    class="text-emerald-500  hover:text-emerald-700  mr-3"
                                >
                                    Disposisi
                                </button>
                                <button
                                    @click="openEdit(item)"
                                    class="text-indigo-500  hover:text-indigo-700  mr-3"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="removeItem(item)"
                                    class="text-red-500  hover:text-red-700 "
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <SuratForm
            v-if="showForm"
            :item="editingItem"
            @submit="handleSubmit"
            @close="showForm = false"
        />
    </div>
</template>
