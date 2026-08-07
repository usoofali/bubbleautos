<script setup lang="ts">
import { FolderOpen } from '@lucide/vue';

interface Props {
    title?: string;
    description?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'No Data Found',
    description: 'There are no records matching your request at this time.',
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mb-4">
            <slot name="icon">
                <FolderOpen class="w-7 h-7" />
            </slot>
        </div>
        <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ title }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mt-1 mb-6">{{ description }}</p>
        <div v-if="$slots.action">
            <slot name="action" />
        </div>
    </div>
</template>
