<script setup>
defineProps({
    title: { type: String, required: true },
    value: { type: String, required: true },
    subtitle: { type: String, default: '' },
    accent: { type: String, default: 'from-cyan-500 to-indigo-500' },
});
</script>

<template>
    <div
        class="relative glass rounded-2xl p-6 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
        :class="accent"
    >
        <div
            class="absolute -top-10 -right-10 w-32 h-32 rounded-full blur-2xl opacity-30"
            :class="accent"
        ></div>
        <p class="relative text-sm font-medium text-slate-500 dark:text-slate-400">{{ title }}</p>
        <p class="relative mt-2 text-3xl font-bold gradient-brand-text">{{ value }}</p>
        <p v-if="subtitle" class="relative mt-1 text-xs text-slate-400 dark:text-slate-500">
            {{ subtitle }}
        </p>
    </div>
</template>