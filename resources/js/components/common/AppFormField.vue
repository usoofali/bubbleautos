<script setup lang="ts">
interface Props {
    label?: string;
    error?: string;
    helpText?: string;
    required?: boolean;
    id?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex flex-col gap-1.5 w-full">
        <label v-if="label" :for="id" class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-0.5">*</span>
        </label>
        <slot />
        <p v-if="error" class="text-xs text-red-500 dark:text-red-400 font-medium mt-0.5 flex items-center gap-1">
            {{ error }}
        </p>
        <p v-else-if="helpText" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
            {{ helpText }}
        </p>
    </div>
</template>
