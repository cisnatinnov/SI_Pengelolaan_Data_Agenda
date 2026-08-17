<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import DisposisiApproveForm from '../components/DisposisiApproveForm.vue';
import DisposisiRejectForm from '../components/DisposisiRejectForm.vue';
import { useToast } from '../composables/useToast';

const props = defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['navigate']);

const user = window.Laravel?.user ?? null;
const isAsistenDaerah = user?.role_slug === 'asisten_daerah';
const toast = useToast();

const items = ref([]);
const loading = ref(false);
const error = ref('');

const approvingItem = ref(null);
const rejectingItem = ref(null);

const keteranganLabels = {
    diterima: { text: 'Diterima', class: 'bg-cyan-500/15 text-cyan-700 ' },
    ditolak: { text: 'Ditolak', class: 'bg-red-500/15 text-red-700 ' },
    diserahkan: { text: 'Diserahkan', class: 'bg-emerald-500/15 text-emerald-700 ' },
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
        const params = props.payload?.surat_id
            ? { surat_id: props.payload.surat_id }
            : {};
        const { data } = await axios.get('/api/disposisi', { params });
        items.value = data;
    } catch (err) {
        error.value = 'Gagal memuat data disposisi.';
    } finally {
        loading.value = false;
    }
};

const approveItem = (item) => {
    approvingItem.value = item;
};

const handleApprove = async (payload) => {
    try {
        await axios.patch(`/api/disposisi/${approvingItem.value.id}`, {
            keterangan: 'diserahkan',
            tandatangan_penerima: payload.tandatangan_penerima,
            tandatangan_dituju: payload.tandatangan_dituju,
        });
        approvingItem.value = null;
        toast.success('Disposisi berhasil diserahkan.');
        await fetchItems();
    } catch (err) {
        toast.error('Gagal menyerahkan disposisi.');
    }
};

const rejectItem = (item) => {
    rejectingItem.value = item;
};

const handleReject = async (payload) => {
    try {
        await axios.patch(`/api/disposisi/${rejectingItem.value.id}`, {
            keterangan: 'ditolak',
            alasan: payload.alasan,
        });
        rejectingItem.value = null;
        toast.success('Disposisi berhasil ditolak.');
        await fetchItems();
    } catch (err) {
        toast.error('Gagal menolak disposisi.');
    }
};

watch(
    () => props.payload,
    () => {
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
                        v-if="payload?.surat_id"
                        class="px-3 py-1 text-xs font-medium text-indigo-600  bg-indigo-500/10 rounded-full hover:bg-indigo-500/20 transition-colors"
                        @click="emit('navigate', 'disposisi')"
                    >
                        Lihat Semua
                    </button>
                </div>
                <p class="mt-1 text-sm text-slate-500 ">
                    Data otomatis dibuat dari Surat
                </p>
            </div>
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
                Belum ada data disposisi.
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Penerima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Dituju</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500  uppercase tracking-wider">Keterangan</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tandatangan_penerima ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ item.tandatangan_dituju ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="keteranganLabels[item.keterangan]?.class ?? 'bg-slate-500/10 text-slate-600 '"
                                >
                                    {{ keteranganLabels[item.keterangan]?.text ?? item.keterangan }}
                                </span>
                                <p
                                    v-if="item.keterangan === 'ditolak' && item.alasan"
                                    class="mt-1 text-xs text-red-600 "
                                >
                                    {{ item.alasan }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <template v-if="isAsistenDaerah">
                                    <button
                                        @click="approveItem(item)"
                                        class="text-emerald-500  hover:text-emerald-700  mr-3"
                                    >
                                        Menyerahkan
                                    </button>
                                    <button
                                        @click="rejectItem(item)"
                                        class="text-red-500  hover:text-red-700 "
                                    >
                                        Menolak
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <DisposisiApproveForm
            v-if="approvingItem"
            :item="approvingItem"
            @submit="handleApprove"
            @close="approvingItem = null"
        />

        <DisposisiRejectForm
            v-if="rejectingItem"
            :item="rejectingItem"
            @submit="handleReject"
            @close="rejectingItem = null"
        />
    </div>
</template>
