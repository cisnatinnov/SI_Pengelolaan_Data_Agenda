<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    item: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['submit', 'close']);

const roles = ref([]);
const roleLoading = ref(false);
const roleError = ref('');

const form = reactive({
    name: '',
    email: '',
    role_id: '',
    password: '',
});

if (props.item) {
    form.name = props.item.name ?? '';
    form.email = props.item.email ?? '';
    form.role_id = props.item.role_id ?? '';
    form.password = '';
}

onMounted(async () => {
    roleLoading.value = true;
    try {
        const { data } = await axios.get('/api/roles');
        roles.value = data;
        if (!form.role_id && roles.value.length > 0) {
            form.role_id = roles.value[0].id;
        }
    } catch (err) {
        roleError.value = 'Gagal memuat daftar role.';
    } finally {
        roleLoading.value = false;
    }
});

const passwordRules = [
    { label: 'Minimal 8 karakter', check: (p) => p.length >= 8 },
    { label: 'Minimal 1 huruf kapital', check: (p) => /[A-Z]/.test(p) },
    { label: 'Minimal 1 angka', check: (p) => /[0-9]/.test(p) },
    { label: 'Minimal 1 karakter unik (simbol)', check: (p) => /[^A-Za-z0-9]/.test(p) },
];

const metCount = computed(() =>
    passwordRules.filter((rule) => rule.check(form.password)).length
);

const strength = computed(() => {
    const count = metCount.value;
    if (count === 0) return { label: 'Kosong', class: 'bg-slate-300', text: 'text-slate-500' };
    if (count <= 2) return { label: 'Lemah', class: 'bg-red-500', text: 'text-red-500' };
    if (count === 3) return { label: 'Sedang', class: 'bg-amber-500', text: 'text-amber-500' };
    return { label: 'Kuat', class: 'bg-emerald-500', text: 'text-emerald-500' };
});

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
                        <label for="role_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Role
                        </label>
                        <p v-if="roleError" class="text-xs text-red-500 mb-1">{{ roleError }}</p>
                        <select
                            id="role_id"
                            v-model="form.role_id"
                            :disabled="roleLoading"
                            required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/15 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 outline-none dark:text-slate-100"
                        >
                            <option v-if="roleLoading" value="" disabled>Memuat role...</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
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

                        <template v-if="form.password">
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-slate-200 dark:bg-white/10">
                                    <div
                                        class="h-full transition-all duration-300"
                                        :class="strength.class"
                                        :style="{ width: (metCount / passwordRules.length) * 100 + '%' }"
                                    ></div>
                                </div>
                                <span class="text-xs font-medium" :class="strength.text">{{ strength.label }}</span>
                            </div>
                            <ul class="mt-2 space-y-1">
                                <li
                                    v-for="rule in passwordRules"
                                    :key="rule.label"
                                    class="flex items-center gap-2 text-xs"
                                    :class="rule.check(form.password) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'"
                                >
                                    <span>{{ rule.check(form.password) ? '✓' : '○' }}</span>
                                    {{ rule.label }}
                                </li>
                            </ul>
                        </template>
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