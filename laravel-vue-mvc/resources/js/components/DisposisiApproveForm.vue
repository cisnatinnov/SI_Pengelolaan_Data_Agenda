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
    tandatangan_penerima: '',
    tandatangan_dituju: '',
});

if (props.item?.tandatangan_penerima) {
    form.tandatangan_penerima = props.item.tandatangan_penerima;
}
if (props.item?.tandatangan_dituju) {
    form.tandatangan_dituju = props.item.tandatangan_dituju;
}

const { errors, validateAll, onInput, fieldClass } = useFormValidation({
    tandatangan_penerima: () =>
        form.tandatangan_penerima.trim() ? null : 'Penerima wajib diisi.',
    tandatangan_dituju: () =>
        form.tandatangan_dituju.trim() ? null : 'Dituju wajib diisi.',
});

function submitForm() {
    const firstKey = validateAll();
    if (firstKey) {
        document.getElementById(firstKey)?.focus();
        return;
    }
    emit('submit', {
        tandatangan_penerima: form.tandatangan_penerima.trim(),
        tandatangan_dituju: form.tandatangan_dituju.trim(),
    });
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-md glass rounded-2xl shadow-2xl">
            <div class="px-6 py-4 border-b border-slate-200/70">
                <h3 class="text-lg font-display font-bold gradient-brand-text">Serahkan Disposisi</h3>
                <p v-if="item" class="mt-1 text-sm text-slate-500">
                    Nomor Surat: {{ item.nomor_surat }}
                </p>
            </div>

            <form novalidate @submit.prevent="submitForm">
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label
                            for="tandatangan_penerima"
                            class="block text-sm font-medium text-slate-700 mb-1"
                        >
                            Penerima
                        </label>
                        <input
                            id="tandatangan_penerima"
                            v-model="form.tandatangan_penerima"
                            type="text"
                            autofocus
                            @input="onInput('tandatangan_penerima')"
                            :class="fieldClass('tandatangan_penerima')"
                        />
                        <p v-if="errors.tandatangan_penerima" class="mt-1 text-xs text-red-500">
                            {{ errors.tandatangan_penerima }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="tandatangan_dituju"
                            class="block text-sm font-medium text-slate-700 mb-1"
                        >
                            Dituju
                        </label>
                        <input
                            id="tandatangan_dituju"
                            v-model="form.tandatangan_dituju"
                            type="text"
                            @input="onInput('tandatangan_dituju')"
                            :class="fieldClass('tandatangan_dituju')"
                        />
                        <p v-if="errors.tandatangan_dituju" class="mt-1 text-xs text-red-500">
                            {{ errors.tandatangan_dituju }}
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200/70 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm font-medium text-white gradient-brand rounded-xl shadow-lg shadow-indigo-500/25 hover:opacity-90 transition-opacity"
                    >
                        Serahkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
