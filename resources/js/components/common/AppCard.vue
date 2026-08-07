<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
    noPadding?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-sm transition-all duration-150 overflow-hidden">
        <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
            <slot name="header">
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ title }}</h3>
                    <p v-if="description" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ description }}</p>
                </div>
            </slot>
            <div v-if="$slots.headerActions" class="flex items-center gap-2">
                <slot name="headerActions" />
            </div>
        </div>
        <div :class="noPadding ? '' : 'p-6'">
            <slot />
        </div>
        <div v-if="$slots.footer" class="px-6 py-3 bg-slate-50/60 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-700/60">
            <slot name="footer" />
        </div>
    </div>
</template>
