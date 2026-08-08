<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
    noPadding?: boolean;
    hoverable?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div
        class="bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl rounded-3xl border border-slate-200/80 dark:border-slate-800/80 shadow-xs dark:shadow-xl dark:shadow-slate-950/40 transition-all duration-200 overflow-hidden"
        :class="{ 'hover:-translate-y-1 hover:border-blue-500/40 dark:hover:border-blue-500/40 hover:shadow-md': hoverable }"
    >
        <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4.5 border-b border-slate-100 dark:border-slate-800/80">
            <slot name="header">
                <div>
                    <h3 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">{{ title }}</h3>
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
        <div v-if="$slots.footer" class="px-6 py-3.5 bg-slate-50/70 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800/80">
            <slot name="footer" />
        </div>
    </div>
</template>
