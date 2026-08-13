<script setup>
import { ref, computed, onMounted } from 'vue';
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
const kegiatanData = ref([]);

const kegiatanPeriode = computed(() => {
    const map = new Map();
    for (const item of kegiatanData.value) {
        const date = new Date(item.tanggal_kegiatan);
        if (Number.isNaN(date.getTime())) continue;

        const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        const label = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(date);

        const entry = map.get(key) ?? { key, label, total: 0, terlaksana: 0, tidak: 0 };
        entry.total++;
        if (item.realisasi_pelaksanaan === 'terlaksana') entry.terlaksana++;
        if (item.realisasi_pelaksanaan === 'tidak') entry.tidak++;
        map.set(key, entry);
    }
    return [...map.values()].sort((a, b) => a.key.localeCompare(b.key));
});

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
        kegiatanData.value = kegiatan;

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

            <div class="mb-6">
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

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Kegiatan per Periode
                </h3>
                <div class="glass rounded-2xl border border-slate-200/70 dark:border-white/10 overflow-hidden">
                    <div
                        v-if="kegiatanPeriode.length === 0"
                        class="p-8 text-center text-sm text-slate-500 dark:text-slate-400"
                    >
                        Belum ada data kegiatan.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10">
                            <thead class="bg-slate-50/70 dark:bg-white/5">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dilaksanakan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tidak Dilaksanakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-white/10">
                                <tr
                                    v-for="periode in kegiatanPeriode"
                                    :key="periode.key"
                                    class="hover:bg-slate-50/60 dark:hover:bg-white/5 transition-colors"
                                >
                                    <td class="px-6 py-4 text-sm font-medium capitalize">{{ periode.label }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ periode.total }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-600 dark:text-emerald-400">{{ periode.terlaksana }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400">{{ periode.tidak }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
