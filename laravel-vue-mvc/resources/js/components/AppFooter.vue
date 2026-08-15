<script setup>
const year = new Date().getFullYear();
</script>

<template>
    <footer class="glass rounded-2xl border border-slate-200/70 dark:border-white/20">
        <div class="px-6 py-5">
            <p class="text-center text-sm text-slate-500 dark:text-slate-400">
                &copy; {{ year }} SI Pengelolaan Data Agenda. All rights reserved.
            </p>
        </div>
    </footer>
</template>