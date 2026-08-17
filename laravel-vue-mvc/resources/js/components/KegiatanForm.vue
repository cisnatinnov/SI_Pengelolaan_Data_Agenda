<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useFormValidation } from '../composables/useFormValidation';

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'close']);

const existingDates = ref(new Set());

const normalizeDate = (value) => (value ?? '').slice(0, 16).replace('T', ' ');

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/kegiatan');
        existingDates.value = new Set(
            data
                .filter((k) => k.id !== props.item?.id)
                .map((k) => normalizeDate(k.tanggal_kegiatan))
        );
    } catch (err) {
        // Server-side validation still applies.
    }
});

const form = reactive({
    nama_kegiatan: '',
    tempat_kegiatan: '',
    tanggal_kegiatan: '',
    uraian_kegiatan: '',
    realisasi_pelaksanaan: 'terlaksana',
    keterangan: '',
    status: 'pelaksanaan',
    nama_penyusun: '',
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
}

watch(
    () => form.realisasi_pelaksanaan,
    (value) => {
        if (value === 'tidak') {
            form.status = 'laporan';
        }
    }
);

const { errors, validateAll, onInput, fieldClass } = useFormValidation({
    nama_kegiatan: () => (form.nama_kegiatan.trim() ? null : 'Nama kegiatan wajib diisi.'),
    tempat_kegiatan: () => (form.tempat_kegiatan.trim() ? null : 'Tempat kegiatan wajib diisi.'),
    tanggal_kegiatan: () => {
        if (!form.tanggal_kegiatan) return 'Tanggal kegiatan wajib diisi.';
        if (existingDates.value.has(normalizeDate(form.tanggal_kegiatan))) {
            return 'Jadwal bentrok: sudah ada kegiatan pada tanggal dan jam tersebut.';
        }
        return null;
    },
    uraian_kegiatan: () => (form.uraian_kegiatan.trim() ? null : 'Uraian kegiatan wajib diisi.'),
});

function submitForm() {
    const firstKey = validateAll();
    if (firstKey) {
        document.getElementById(firstKey)?.focus();
        return;
    }
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
        <div class="w-full max-w-lg glass  rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-200 ">
                <h3 class="text-lg font-display font-bold gradient-brand-text">
                    {{ item ? 'Edit Kegiatan' : 'Tambah Kegiatan' }}
                </h3>
            </div>

            <form novalidate @submit.prevent="submitForm">
                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div
                        v-for="field in fields"
                        :key="field.key"
                        :class="field.span === 2 ? 'sm:col-span-2' : 'sm:col-span-1'"
                    >
                        <label
                            :for="field.key"
                            class="block text-sm font-medium text-slate-700  mb-1"
                        >
                            {{ field.label }}
                        </label>
                        <input
                            :id="field.key"
                            v-model="form[field.key]"
                            :type="field.type"
                            required
                            @input="onInput(field.key)"
                            :class="fieldClass(field.key)"
                        />
                        <p v-if="errors[field.key]" class="mt-1 text-xs text-red-500">
                            {{ errors[field.key] }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="uraian_kegiatan"
                            class="block text-sm font-medium text-slate-700  mb-1"
                        >
                            Uraian Kegiatan
                        </label>
                        <textarea
                            id="uraian_kegiatan"
                            v-model="form.uraian_kegiatan"
                            required
                            rows="3"
                            @input="onInput('uraian_kegiatan')"
                            :class="fieldClass('uraian_kegiatan')"
                        ></textarea>
                        <p v-if="errors.uraian_kegiatan" class="mt-1 text-xs text-red-500">
                            {{ errors.uraian_kegiatan }}
                        </p>
                    </div>

                    <div class="sm:col-span-1">
                        <label
                            for="realisasi_pelaksanaan"
                            class="block text-sm font-medium text-slate-700  mb-1"
                        >
                            Realisasi Pelaksanaan
                        </label>
                        <select
                            id="realisasi_pelaksanaan"
                            v-model="form.realisasi_pelaksanaan"
                            class="w-full rounded-xl border border-slate-300  bg-white  px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none "
                        >
                            <option value="terlaksana">Terlaksana</option>
                            <option value="tidak">Tidak Terlaksana</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1">
                        <label
                            for="status"
                            class="block text-sm font-medium text-slate-700  mb-1"
                        >
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-xl border border-slate-300  bg-white  px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none "
                        >
                            <option value="pelaksanaan">Pelaksanaan</option>
                            <option value="laporan">Laporan</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="keterangan"
                            class="block text-sm font-medium text-slate-700  mb-1"
                        >
                            Keterangan
                        </label>
                        <textarea
                            id="keterangan"
                            v-model="form.keterangan"
                            rows="2"
                            class="w-full rounded-xl border border-slate-300  bg-white  px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none "
                        ></textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="nama_penyusun"
                            class="block text-sm font-medium text-slate-700  mb-1"
                        >
                            Nama Penyusun
                        </label>
                        <input
                            id="nama_penyusun"
                            v-model="form.nama_penyusun"
                            type="text"
                            class="w-full rounded-xl border border-slate-300  bg-white  px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none "
                        />
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200  flex justify-end gap-3">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="px-4 py-2 text-sm font-medium text-slate-700  bg-slate-100  rounded-xl hover:bg-slate-200  transition-colors"
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