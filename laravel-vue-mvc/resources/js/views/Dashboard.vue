<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import StatCard from '../components/StatCard.vue';

defineProps({
    payload: {
        type: Object,
        default: null,
    },
});

const loading = ref(false);
const error = ref('');
const stats = ref([]);

const fetchStats = async () => {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get('/api/surat-kegiatan');
        const diterima = data.filter((item) => item.keterangan === 'diterima').length;
        const ditolak = data.filter((item) => item.keterangan === 'ditolak').length;
        const disahkan = data.filter((item) => item.keterangan === 'disahkan').length;
        stats.value = [
            {
                title: 'Diterima',
                value: String(diterima),
                subtitle: 'Disposisi diterima',
                accent: 'from-cyan-500 to-indigo-500',
            },
            {
                title: 'Ditolak',
                value: String(ditolak),
                subtitle: 'Disposisi ditolak',
                accent: 'from-red-500 to-rose-500',
            },
            {
                title: 'Disahkan',
                value: String(disahkan),
                subtitle: 'Disposisi disahkan',
                accent: 'from-emerald-500 to-teal-500',
            },
        ];
    } catch (err) {
        error.value = 'Gagal memuat statistik.';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchStats);
</script>

<template>
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-display font-bold gradient-brand-text">Dashboard</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Statistik keterangan disposisi
            </p>
        </div>

        <div
            v-if="error"
            class="mb-4 p-4 bg-red-50/80 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300 text-sm rounded-xl backdrop-blur"
        >
            {{ error }}
        </div>

        <div v-if="loading" class="glass rounded-2xl p-8 text-center text-sm text-slate-500 dark:text-slate-400">
            Memuat data...
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <StatCard
                v-for="stat in stats"
                :key="stat.title"
                :title="stat.title"
                :value="stat.value"
                :subtitle="stat.subtitle"
                :accent="stat.accent"
            />
        </div>
    </div>
</template>