<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    status?: string;
    variant?: 'blue' | 'emerald' | 'amber' | 'slate' | 'red' | 'purple' | 'indigo';
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    status: '',
    size: 'sm',
});

const resolvedVariant = computed(() => {
    if (props.variant) return props.variant;

    const s = props.status?.toLowerCase();
    switch (s) {
        case 'delivered':
        case 'completed':
        case 'paid':
        case 'matched':
            return 'emerald';
        case 'in_transit':
        case 'dispatched':
        case 'partial':
        case 'received':
            return 'blue';
        case 'pending':
        case 'needs_review':
        case 'unpaid':
            return 'amber';
        case 'cancelled':
        case 'archived':
            return 'red';
        default:
            return 'slate';
    }
});

const badgeClasses = computed(() => {
    const size = props.size === 'sm' ? 'px-2.5 py-0.5 text-xs' : 'px-3 py-1 text-sm';
    switch (resolvedVariant.value) {
        case 'blue':
            return `${size} bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800/50`;
        case 'emerald':
            return `${size} bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800/50`;
        case 'amber':
            return `${size} bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800/50`;
        case 'red':
            return `${size} bg-red-50 text-red-700 border border-red-200/80 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800/50`;
        case 'purple':
            return `${size} bg-purple-50 text-purple-700 border border-purple-200/80 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800/50`;
        case 'indigo':
            return `${size} bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800/50`;
        case 'slate':
        default:
            return `${size} bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700`;
    }
});

const formattedLabel = computed(() => {
    if (!props.status) return '';
    return props.status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
});
</script>

<template>
    <span class="inline-flex items-center font-medium rounded-full tracking-wide" :class="badgeClasses">
        <slot>{{ formattedLabel }}</slot>
    </span>
</template>
