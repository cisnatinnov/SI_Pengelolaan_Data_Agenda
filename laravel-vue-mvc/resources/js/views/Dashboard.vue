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
const disposisiStats = ref([]);
const kegiatanStats = ref([]);

const fetchStats = async () => {
    loading.value = true;
    error.value = '';
    try {
        const [disposisiRes, kegiatanRes] = await Promise.all([
            axios.get('/api/disposisi'),
            axios.get('/api/kegiatan'),
        ]);

        const disposisi = disposisiRes.data;
        const kegiatan = kegiatanRes.data;

        const count = (arr, key, value) => arr.filter((item) => item[key] === value).length;

        disposisiStats.value = [
            {
                title: 'Total',
                value: String(disposisi.length),
                subtitle: 'Disposisi',
                accent: 'from-slate-400 to-slate-500',
            },
            {
                title: 'Diterima',
                value: String(count(disposisi, 'keterangan', 'diterima')),
                subtitle: 'Disposisi diterima',
                accent: 'from-cyan-500 to-indigo-500',
            },
            {
                title: 'Ditolak',
                value: String(count(disposisi, 'keterangan', 'ditolak')),
                subtitle: 'Disposisi ditolak',
                accent: 'from-red-500 to-rose-500',
            },
            {
                title: 'Disahkan',
                value: String(count(disposisi, 'keterangan', 'disahkan')),
                subtitle: 'Disposisi disahkan',
                accent: 'from-emerald-500 to-teal-500',
            },
        ];

        kegiatanStats.value = [
            {
                title: 'Total',
                value: String(kegiatan.length),
                subtitle: 'Kegiatan',
                accent: 'from-indigo-500 to-purple-500',
            },
            {
                title: 'Dilaksanakan',
                value: String(count(kegiatan, 'realisasi_pelaksanaan', 'terlaksana')),
                subtitle: 'Kegiatan terlaksana',
                accent: 'from-emerald-500 to-teal-500',
            },
            {
                title: 'Tidak Dilaksanakan',
                value: String(count(kegiatan, 'realisasi_pelaksanaan', 'tidak')),
                subtitle: 'Kegiatan tidak terlaksana',
                accent: 'from-red-500 to-rose-500',
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
                Ringkasan statistik disposisi dan kegiatan
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

        <template v-else>
            <div class="mb-6">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Disposisi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <StatCard
                        v-for="stat in disposisiStats"
                        :key="stat.title"
                        :title="stat.title"
                        :value="stat.value"
                        :subtitle="stat.subtitle"
                        :accent="stat.accent"
                    />
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Kegiatan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <StatCard
                        v-for="stat in kegiatanStats"
                        :key="stat.title"
                        :title="stat.title"
                        :value="stat.value"
                        :subtitle="stat.subtitle"
                        :accent="stat.accent"
                    />
                </div>
            </div>
        </template>
    </div>
</template>
