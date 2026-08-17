<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PengingatForm from '../components/PengingatForm.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const items = ref([]);
const loading = ref(false);
const error = ref('');
const toast = useToast();
const { confirm } = useConfirm();

const showForm = ref(false);
const editingItem = ref(null);

const prioritasLabels = {
    rendah: { text: 'Rendah', class: 'bg-slate-500/10 text-slate-600 ' },
    sedang: { text: 'Sedang', class: 'bg-amber-500/15 text-amber-700 ' },
    tinggi: { text: 'Tinggi', class: 'bg-red-500/15 text-red-700 ' },
};

const statusLabels = {
    pending: { text: 'Pending', class: 'bg-cyan-500/15 text-cyan-700 ' },
    selesai: { text: 'Selesai', class: 'bg-emerald-500/15 text-emerald-700 ' },
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
        const { data } = await axios.get('/api/pengingat');
        items.value = data;
    } catch (err) {
        error.value = 'Gagal memuat data pengingat.';
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
            await axios.put(`/api/pengingat/${editingItem.value.id}`, payload);
            toast.success('Pengingat berhasil diperbarui.');
        } else {
            await axios.post('/api/pengingat', payload);
            toast.success('Pengingat berhasil ditambahkan.');
        }
        showForm.value = false;
        await fetchItems();
    } catch (err) {
        toast.error('Gagal menyimpan data pengingat.');
    }
};

const removeItem = async (item) => {
    const ok = await confirm({
        title: 'Hapus Pengingat',
        message: `Hapus pengingat "${item.judul}"?`,
        confirmText: 'Hapus',
        danger: true,
    });
    if (!ok) return;
    try {
        await axios.delete(`/api/pengingat/${item.id}`);
        toast.success('Pengingat berhasil dihapus.');
        await fetchItems();
    } catch (err) {
        toast.error('Gagal menghapus pengingat.');
    }
};

onMounted(fetchItems);
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-display font-bold gradient-brand-text">Pengingat</h2>
                <p class="mt-1 text-sm text-slate-500 ">Kelola data pengingat</p>
            </div>
            <button
                @click="openCreate"
                class="px-4 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
            >
                + Tambah Pengingat
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
                Belum ada data pengingat.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 ">
                    <thead class="bg-slate-50/70 ">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Tanggal Pengingat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Prioritas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Status</th>
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
                            <td class="px-6 py-4 text-sm font-medium">{{ item.judul }}</td>
                            <td class="px-6 py-4 text-sm max-w-xs">
                                <p class="truncate" :title="item.deskripsi">{{ item.deskripsi || '-' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatTanggal(item.tanggal_pengingat) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="prioritasLabels[item.prioritas]?.class ?? 'bg-slate-500/10 text-slate-600 '"
                                >
                                    {{ prioritasLabels[item.prioritas]?.text ?? item.prioritas }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="statusLabels[item.status]?.class ?? 'bg-slate-500/10 text-slate-600 '"
                                >
                                    {{ statusLabels[item.status]?.text ?? item.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
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

        <PengingatForm
            v-if="showForm"
            :item="editingItem"
            @submit="handleSubmit"
            @close="showForm = false"
        />
    </div>
</template>
