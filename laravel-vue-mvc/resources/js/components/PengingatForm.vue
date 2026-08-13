<script setup>
import { reactive } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'close']);

const form = reactive({
    judul: '',
    deskripsi: '',
    tanggal_pengingat: '',
    prioritas: 'sedang',
    status: 'pending',
});

if (props.item) {
    form.judul = props.item.judul ?? '';
    form.deskripsi = props.item.deskripsi ?? '';
    form.tanggal_pengingat = props.item.tanggal_pengingat?.slice(0, 16) ?? '';
    form.prioritas = props.item.prioritas ?? 'sedang';
    form.status = props.item.status ?? 'pending';
}

function submitForm() {
    emit('submit', { ...form });
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-lg glass dark:glass-dark rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-white/10">
                <h3 class="text-lg font-display font-bold gradient-brand-text">
                    {{ item ? 'Edit Pengingat' : 'Tambah Pengingat' }}
                </h3>
            </div>

            <form @submit.prevent="submitForm">
                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="judul" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Judul
                        </label>
                        <input
                            id="judul"
                            v-model="form.judul"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="deskripsi" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Deskripsi
                        </label>
                        <textarea
                            id="deskripsi"
                            v-model="form.deskripsi"
                            rows="3"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        ></textarea>
                    </div>

                    <div>
                        <label for="tanggal_pengingat" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Tanggal Pengingat
                        </label>
                        <input
                            id="tanggal_pengingat"
                            v-model="form.tanggal_pengingat"
                            type="datetime-local"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        />
                    </div>

                    <div>
                        <label for="prioritas" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Prioritas
                        </label>
                        <select
                            id="prioritas"
                            v-model="form.prioritas"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option value="rendah">Rendah</option>
                            <option value="sedang">Sedang</option>
                            <option value="tinggi">Tinggi</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option value="pending">Pending</option>
                            <option value="selesai">Selesai</option>
                        </select>
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
