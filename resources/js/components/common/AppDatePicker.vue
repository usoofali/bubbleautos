<script setup lang="ts">
interface Props {
    modelValue?: string | null;
    placeholder?: string;
    disabled?: boolean;
    error?: boolean | string;
    id?: string;
}

defineProps<Props>();

const emit = defineEmits(['update:modelValue']);

const onInput = (e: Event) => {
    emit('update:modelValue', (e.target as HTMLInputElement).value);
};
</script>

<template>
    <input
        :id="id"
        type="date"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="onInput"
        class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 transition-all disabled:opacity-60"
        :class="
            error
                ? 'border-red-300 dark:border-red-700 focus:border-red-500 focus:ring-red-200'
                : 'border-slate-300 dark:border-slate-700 focus:border-blue-500 focus:ring-blue-100 dark:focus:ring-blue-900/40'
        "
    />
</template>
