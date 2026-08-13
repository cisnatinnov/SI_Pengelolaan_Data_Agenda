<script setup>
import { reactive } from 'vue';
import { useFormValidation } from '../composables/useFormValidation';

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'close']);

const form = reactive({
    tanggal: '',
    nomor_surat: '',
    asal_surat: '',
    perihal: '',
    kepada: '',
    pembawa_surat: '',
    tandatangan_penerima: '',
    tandatangan_dituju: '',
    keterangan: 'diterima',
    alasan: '',
});

if (props.item) {
    form.tanggal = props.item.tanggal?.slice(0, 16) ?? '';
    form.nomor_surat = props.item.nomor_surat ?? '';
    form.asal_surat = props.item.asal_surat ?? '';
    form.perihal = props.item.perihal ?? '';
    form.kepada = props.item.kepada ?? '';
    form.pembawa_surat = props.item.pembawa_surat ?? '';
    form.tandatangan_penerima = props.item.tandatangan_penerima ?? '';
    form.tandatangan_dituju = props.item.tandatangan_dituju ?? '';
    form.keterangan = props.item.keterangan ?? 'diterima';
    form.alasan = props.item.alasan ?? '';
}

const { errors, validateAll, onInput, fieldClass } = useFormValidation({
    tanggal: () => (form.tanggal ? null : 'Tanggal wajib diisi.'),
    nomor_surat: () => (form.nomor_surat.trim() ? null : 'Nomor surat wajib diisi.'),
    asal_surat: () => (form.asal_surat.trim() ? null : 'Asal surat wajib diisi.'),
    perihal: () => (form.perihal.trim() ? null : 'Perihal wajib diisi.'),
    kepada: () => (form.kepada.trim() ? null : 'Kepada wajib diisi.'),
    pembawa_surat: () => (form.pembawa_surat.trim() ? null : 'Pembawa surat wajib diisi.'),
    alasan: () =>
        form.keterangan === 'ditolak' && !form.alasan.trim() ? 'Alasan penolakan wajib diisi.' : null,
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
    { key: 'tanggal', label: 'Tanggal', type: 'datetime-local' },
    { key: 'nomor_surat', label: 'Nomor Surat', type: 'text' },
    { key: 'asal_surat', label: 'Asal Surat', type: 'text' },
    { key: 'perihal', label: 'Perihal', type: 'text' },
    { key: 'kepada', label: 'Kepada', type: 'text' },
    { key: 'pembawa_surat', label: 'Pembawa Surat', type: 'text' },
    { key: 'tandatangan_penerima', label: 'Penerima', type: 'text' },
    { key: 'tandatangan_dituju', label: 'Dituju', type: 'text' },
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
                    {{ item ? 'Edit Disposisi' : 'Tambah Disposisi' }}
                </h3>
            </div>

            <form novalidate @submit.prevent="submitForm">
                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="field in fields" :key="field.key" class="sm:col-span-1">
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
                            :required="!field.key.startsWith('tandatangan')"
                            @input="onInput(field.key)"
                            :class="fieldClass(field.key)"
                        />
                        <p v-if="errors[field.key]" class="mt-1 text-xs text-red-500">
                            {{ errors[field.key] }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label
                            for="keterangan"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            Keterangan
                        </label>
                        <select
                            id="keterangan"
                            v-model="form.keterangan"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option value="diterima">Diterima</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="disahkan">Disahkan</option>
                        </select>
                    </div>

                    <div v-if="form.keterangan === 'ditolak'" class="sm:col-span-2">
                        <label
                            for="alasan"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                        >
                            Alasan Ditolak
                        </label>
                        <textarea
                            id="alasan"
                            v-model="form.alasan"
                            required
                            rows="3"
                            @input="onInput('alasan')"
                            :class="fieldClass('alasan')"
                        ></textarea>
                        <p v-if="errors.alasan" class="mt-1 text-xs text-red-500">
                            {{ errors.alasan }}
                        </p>
                    </div>
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
