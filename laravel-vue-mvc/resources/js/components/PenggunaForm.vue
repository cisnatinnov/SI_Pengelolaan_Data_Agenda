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
    name: '',
    email: '',
    role: 'staff',
    password: '',
});

if (props.item) {
    form.name = props.item.name ?? '';
    form.email = props.item.email ?? '';
    form.role = props.item.role ?? 'staff';
    form.password = '';
}

function submitForm() {
    const payload = { ...form };
    if (!payload.password) {
        delete payload.password;
    }
    emit('submit', payload);
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-md glass dark:glass-dark rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-white/10">
                <h3 class="text-lg font-display font-bold gradient-brand-text">
                    {{ item ? 'Edit Pengguna' : 'Tambah Pengguna' }}
                </h3>
            </div>

            <form @submit.prevent="submitForm">
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Nama
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        />
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Role
                        </label>
                        <select
                            id="role"
                            v-model="form.role"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="asisten_daerah">Asisten Daerah</option>
                            <option value="opd">OPD</option>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Password
                            <span v-if="item" class="text-xs text-slate-400 font-normal">(kosongkan jika tidak diubah)</span>
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            :required="!item"
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        />
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