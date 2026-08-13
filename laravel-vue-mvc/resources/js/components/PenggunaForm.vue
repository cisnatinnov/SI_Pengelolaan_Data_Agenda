<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useFormValidation } from '../composables/useFormValidation';

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

const showPassword = ref(false);

const missingRules = computed(() =>
    passwordRules.filter((rule) => !rule.check(form.password))
);

const { errors, validateAll, onInput, fieldClass } = useFormValidation({
    name: () => (form.name.trim() ? null : 'Nama wajib diisi.'),
    email: () => {
        if (!form.email.trim()) return 'Email wajib diisi.';
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
            ? null
            : 'Format email tidak valid.';
    },
    role_id: () => (form.role_id ? null : 'Role wajib dipilih.'),
    password: () => {
        if (!props.item && !form.password) return 'Password wajib diisi.';
        if (form.password && metCount.value < passwordRules.length) {
            return 'Password harus memenuhi semua ketentuan.';
        }
        return null;
    },
});

function submitForm() {
    const firstKey = validateAll();
    if (firstKey) {
        document.getElementById(firstKey)?.focus();
        return;
    }

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

            <form novalidate @submit.prevent="submitForm">
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
                            @input="onInput('name')"
                            :class="fieldClass('name')"
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-500">
                            {{ errors.name }}
                        </p>
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
                            @input="onInput('email')"
                            :class="fieldClass('email')"
                        />
                        <p v-if="errors.email" class="mt-1 text-xs text-red-500">
                            {{ errors.email }}
                        </p>
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
                            @input="onInput('role_id')"
                            :class="fieldClass('role_id')"
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
                        <p v-if="errors.role_id" class="mt-1 text-xs text-red-500">
                            {{ errors.role_id }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Password
                            <span v-if="item" class="text-xs text-slate-400 font-normal">(kosongkan jika tidak diubah)</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                :required="!item"
                                minlength="8"
                                autocomplete="new-password"
                                @input="onInput('password')"
                                :class="fieldClass('password')"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                                @click="showPassword = !showPassword"
                            >
                                <svg
                                    v-if="showPassword"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="w-5 h-5"
                                >
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M1 1l22 22" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <svg
                                    v-else
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="w-5 h-5"
                                >
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="mt-1 text-xs text-red-500">
                            {{ errors.password }}
                        </p>

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
                                    :class="rule.check(form.password) ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'"
                                >
                                    <span>{{ rule.check(form.password) ? '✓' : '○' }}</span>
                                    {{ rule.label }}
                                </li>
                            </ul>
                            <p
                                v-if="missingRules.length > 0"
                                class="mt-2 text-xs font-medium text-amber-600 dark:text-amber-400"
                            >
                                Masih kurang: {{ missingRules.map((rule) => rule.label).join(', ') }}
                            </p>
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