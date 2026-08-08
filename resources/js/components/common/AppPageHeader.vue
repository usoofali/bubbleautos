<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ title }}</h1>
            <p v-if="description" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3 shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>
