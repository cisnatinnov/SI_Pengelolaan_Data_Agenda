<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'close']);

const form = reactive({
    nama_kegiatan: '',
    tempat_kegiatan: '',
    tanggal_kegiatan: '',
    uraian_kegiatan: '',
    realisasi_pelaksanaan: 'terlaksana',
    keterangan: '',
    status: 'pelaksanaan',
    nama_penyusun: '',
    tanda_tangan_penyusun: '',
});

if (props.item) {
    form.nama_kegiatan = props.item.nama_kegiatan ?? '';
    form.tempat_kegiatan = props.item.tempat_kegiatan ?? '';
    form.tanggal_kegiatan = props.item.tanggal_kegiatan?.slice(0, 16) ?? '';
    form.uraian_kegiatan = props.item.uraian_kegiatan ?? '';
    form.realisasi_pelaksanaan = props.item.realisasi_pelaksanaan ?? 'terlaksana';
    form.keterangan = props.item.keterangan ?? '';
    form.status = props.item.status ?? 'pelaksanaan';
    form.nama_penyusun = props.item.nama_penyusun ?? '';
    form.tanda_tangan_penyusun = props.item.tanda_tangan_penyusun ?? '';
}

watch(
    () => form.status,
    (status) => {
        if (status !== 'laporan') {
            form.nama_penyusun = '';
            form.tanda_tangan_penyusun = '';
        }
    }
);

function submitForm() {
    emit('submit', { ...form });
}

const fields = [
    { key: 'nama_kegiatan', label: 'Nama Kegiatan', type: 'text', span: 2 },
    { key: 'tempat_kegiatan', label: 'Tempat Kegiatan', type: 'text', span: 1 },
    { key: 'tanggal_kegiatan', label: 'Tanggal Kegiatan', type: 'datetime-local', span: 1 },
];
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-lg glass dark:glass-dark rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-white/10">
                <h3 class="text-lg font-display font-bold gradient-brand-text">
                    {{ item ? 'Edit Kegiatan' : 'Tambah Kegiatan' }}
                </h3>
            </div>

            <form @submit.prevent="submitForm">
                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div
                        v-for="field in fields"
                        :key="field.key"
                        :class="field.span === 2 ? 'sm:col-span-2' : 'sm:col-span-1'"
                    >
                        <label
                            :for="field.key"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            {{ field.label }}
                        </label>
                        <input
                            :id="field.key"
                            v-model="form[field.key]"
                            :type="field.type"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="uraian_kegiatan"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            Uraian Kegiatan
                        </label>
                        <textarea
                            id="uraian_kegiatan"
                            v-model="form.uraian_kegiatan"
                            required
                            rows="3"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        ></textarea>
                    </div>

                    <div class="sm:col-span-1">
                        <label
                            for="realisasi_pelaksanaan"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            Realisasi Pelaksanaan
                        </label>
                        <select
                            id="realisasi_pelaksanaan"
                            v-model="form.realisasi_pelaksanaan"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option value="terlaksana">Terlaksana</option>
                            <option value="tidak">Tidak</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <label
                            for="status"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option value="pelaksanaan">Pelaksanaan</option>
                            <option value="laporan">Laporan</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="keterangan"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            Keterangan
                        </label>
                        <textarea
                            id="keterangan"
                            v-model="form.keterangan"
                            rows="2"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        ></textarea>
                    </div>

                    <template v-if="form.status === 'laporan'">
                        <div class="sm:col-span-1">
                            <label
                                for="nama_penyusun"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                            >
                                Nama Penyusun
                            </label>
                            <input
                                id="nama_penyusun"
                                v-model="form.nama_penyusun"
                                type="text"
                                required
                                class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                            />
                        </div>
                        <div class="sm:col-span-1">
                            <label
                                for="tanda_tangan_penyusun"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                            >
                                Tanda Tangan Penyusun
                            </label>
                            <input
                                id="tanda_tangan_penyusun"
                                v-model="form.tanda_tangan_penyusun"
                                type="text"
                                required
                                class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                            />
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 dark:border-white/10 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-white/10 rounded-xl hover:bg-slate-200 dark:hover:bg-white/15 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>