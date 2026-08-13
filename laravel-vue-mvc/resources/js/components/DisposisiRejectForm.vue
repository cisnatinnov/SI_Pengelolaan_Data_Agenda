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
    alasan: '',
});

if (props.item?.alasan) {
    form.alasan = props.item.alasan;
}

const { errors, validateAll, onInput, fieldClass } = useFormValidation({
    alasan: () => (form.alasan.trim() ? null : 'Alasan penolakan wajib diisi.'),
});

function submitForm() {
    const firstKey = validateAll();
    if (firstKey) {
        document.getElementById(firstKey)?.focus();
        return;
    }
    emit('submit', { alasan: form.alasan.trim() });
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-md glass dark:glass-dark rounded-2xl shadow-2xl">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-white/10">
                <h3 class="text-lg font-display font-bold gradient-brand-text">Tolak Disposisi</h3>
                <p v-if="item" class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Nomor Surat: {{ item.nomor_surat }}
                </p>
            </div>

            <form novalidate @submit.prevent="submitForm">
                <div class="px-6 py-4">
                    <label
                        for="alasan"
                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                    >
                        Alasan Penolakan
                    </label>
                    <textarea
                        id="alasan"
                        v-model="form.alasan"
                        rows="3"
                        autofocus
                        @input="onInput('alasan')"
                        :class="fieldClass('alasan')"
                    ></textarea>
                    <p v-if="errors.alasan" class="mt-1 text-xs text-red-500">
                        {{ errors.alasan }}
                    </p>
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
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
