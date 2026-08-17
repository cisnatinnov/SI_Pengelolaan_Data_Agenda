<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PenggunaForm from '../components/PenggunaForm.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const currentUser = window.Laravel?.user ?? null;
const toast = useToast();
const { confirm } = useConfirm();

const items = ref([]);
const loading = ref(false);
const error = ref('');

const showForm = ref(false);
const editingItem = ref(null);

const roleLabels = {
    admin: { text: 'Admin', class: 'bg-fuchsia-500/15 text-fuchsia-700 ' },
    staff: { text: 'Staff', class: 'bg-cyan-500/15 text-cyan-700 ' },
    asisten_daerah: { text: 'Asisten Daerah', class: 'bg-indigo-500/15 text-indigo-700 ' },
    opd: { text: 'OPD', class: 'bg-emerald-500/15 text-emerald-700 ' },
};

const fetchItems = async () => {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get('/api/users');
        items.value = data;
    } catch (err) {
        error.value = 'Gagal memuat data pengguna.';
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
    error.value = '';
    try {
        if (editingItem.value) {
            await axios.put(`/api/users/${editingItem.value.id}`, payload);
            toast.success('Pengguna berhasil diperbarui.');
        } else {
            await axios.post('/api/users', payload);
            toast.success('Pengguna berhasil ditambahkan.');
        }
        showForm.value = false;
        await fetchItems();
    } catch (err) {
        toast.error(err.response?.data?.message ?? 'Gagal menyimpan data pengguna.');
    }
};

const removeItem = async (item) => {
    const ok = await confirm({
        title: 'Hapus Pengguna',
        message: `Hapus pengguna "${item.name}"?`,
        confirmText: 'Hapus',
        danger: true,
    });
    if (!ok) return;
    error.value = '';
    try {
        await axios.delete(`/api/users/${item.id}`);
        toast.success('Pengguna berhasil dihapus.');
        await fetchItems();
    } catch (err) {
        toast.error(err.response?.data?.message ?? 'Gagal menghapus pengguna.');
    }
};

onMounted(fetchItems);
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-display font-bold gradient-brand-text">Pengguna</h2>
                <p class="mt-1 text-sm text-slate-500 ">Kelola akun pengguna</p>
            </div>
            <button
                @click="openCreate"
                class="px-4 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
            >
                + Tambah Pengguna
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
                Belum ada data pengguna.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 ">
                    <thead class="bg-slate-50/70 ">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Role</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ item.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="roleLabels[item.role_slug]?.class ?? 'bg-slate-500/10 text-slate-600 '"
                                >
                                    {{ roleLabels[item.role_slug]?.text ?? item.role?.name }}
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
                                    v-if="currentUser?.email !== item.email"
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

        <PenggunaForm
            v-if="showForm"
            :item="editingItem"
            @submit="handleSubmit"
            @close="showForm = false"
        />
    </div>
</template>