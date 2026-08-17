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
    tanggal_pelaksanaan: '',
    tempat_pelaksanaan: '',
    pembawa_surat: '',
    tandatangan: '',
});

if (props.item) {
    form.tanggal = props.item.tanggal?.slice(0, 16) ?? '';
    form.nomor_surat = props.item.nomor_surat ?? '';
    form.asal_surat = props.item.asal_surat ?? '';
    form.perihal = props.item.perihal ?? '';
    form.kepada = props.item.kepada ?? '';
    form.tanggal_pelaksanaan = props.item.tanggal_pelaksanaan?.slice(0, 16) ?? '';
    form.tempat_pelaksanaan = props.item.tempat_pelaksanaan ?? '';
    form.pembawa_surat = props.item.pembawa_surat ?? '';
    form.tandatangan = props.item.tandatangan ?? '';
}

const { errors, validateAll, onInput, fieldClass } = useFormValidation({
    tanggal: () => (form.tanggal ? null : 'Tanggal wajib diisi.'),
    nomor_surat: () => (form.nomor_surat.trim() ? null : 'Nomor surat wajib diisi.'),
    asal_surat: () => (form.asal_surat.trim() ? null : 'Asal surat wajib diisi.'),
    perihal: () => (form.perihal.trim() ? null : 'Perihal wajib diisi.'),
    kepada: () => (form.kepada.trim() ? null : 'Kepada wajib diisi.'),
    tanggal_pelaksanaan: () => (form.tanggal_pelaksanaan ? null : 'Tanggal pelaksanaan wajib diisi.'),
    tempat_pelaksanaan: () => (form.tempat_pelaksanaan.trim() ? null : 'Tempat pelaksanaan wajib diisi.'),
    pembawa_surat: () => (form.pembawa_surat.trim() ? null : 'Pembawa surat wajib diisi.'),
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
    { key: 'tanggal', label: 'Tanggal', type: 'datetime-local', span: 1 },
    { key: 'nomor_surat', label: 'Nomor Surat', type: 'text', span: 1 },
    { key: 'asal_surat', label: 'Asal Surat', type: 'text', span: 1 },
    { key: 'perihal', label: 'Perihal', type: 'text', span: 1 },
    { key: 'kepada', label: 'Kepada', type: 'text', span: 1 },
    { key: 'tanggal_pelaksanaan', label: 'Tanggal Pelaksanaan', type: 'datetime-local', span: 1 },
    { key: 'tempat_pelaksanaan', label: 'Tempat Pelaksanaan', type: 'text', span: 2 },
    { key: 'pembawa_surat', label: 'Pembawa Surat', type: 'text', span: 1 },
    { key: 'tandatangan', label: 'Tandatangan', type: 'text', span: 1, optional: true },
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
                    {{ item ? 'Edit Surat' : 'Tambah Surat' }}
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
                            :required="!field.optional"
                            @input="onInput(field.key)"
                            :class="fieldClass(field.key)"
                        />
                        <p v-if="errors[field.key]" class="mt-1 text-xs text-red-500">
                            {{ errors[field.key] }}
                        </p>
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
