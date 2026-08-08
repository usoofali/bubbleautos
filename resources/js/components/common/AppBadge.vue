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
        case 'partially_paid':
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
    const size = props.size === 'sm' ? 'px-2.5 py-0.5 text-xs gap-1.5' : 'px-3.5 py-1 text-sm gap-2';
    switch (resolvedVariant.value) {
        case 'blue':
            return `${size} bg-blue-50 text-blue-700 border border-blue-200/90 dark:bg-blue-950/70 dark:text-blue-300 dark:border-blue-800/60`;
        case 'emerald':
            return `${size} bg-emerald-50 text-emerald-700 border border-emerald-200/90 dark:bg-emerald-950/70 dark:text-emerald-300 dark:border-emerald-800/60`;
        case 'amber':
            return `${size} bg-amber-50 text-amber-800 border border-amber-200/90 dark:bg-amber-950/70 dark:text-amber-300 dark:border-amber-800/60`;
        case 'red':
            return `${size} bg-red-50 text-red-700 border border-red-200/90 dark:bg-red-950/70 dark:text-red-300 dark:border-red-800/60`;
        case 'purple':
            return `${size} bg-purple-50 text-purple-700 border border-purple-200/90 dark:bg-purple-950/70 dark:text-purple-300 dark:border-purple-800/60`;
        case 'indigo':
            return `${size} bg-indigo-50 text-indigo-700 border border-indigo-200/90 dark:bg-indigo-950/70 dark:text-indigo-300 dark:border-indigo-800/60`;
        case 'slate':
        default:
            return `${size} bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800`;
    }
});

const dotColorClass = computed(() => {
    switch (resolvedVariant.value) {
        case 'blue':
            return 'bg-blue-500 dark:bg-blue-400';
        case 'emerald':
            return 'bg-emerald-500 dark:bg-emerald-400';
        case 'amber':
            return 'bg-amber-500 dark:bg-amber-400';
        case 'red':
            return 'bg-red-500 dark:bg-red-400';
        case 'purple':
            return 'bg-purple-500 dark:bg-purple-400';
        case 'indigo':
            return 'bg-indigo-500 dark:bg-indigo-400';
        case 'slate':
        default:
            return 'bg-slate-400 dark:bg-slate-500';
    }
});

const formattedLabel = computed(() => {
    if (!props.status) return '';
    return props.status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
});
</script>

<template>
    <span class="inline-flex items-center font-bold rounded-full tracking-wide shadow-2xs shrink-0 whitespace-nowrap" :class="badgeClasses">
        <span class="w-1.5 h-1.5 rounded-full animate-pulse shrink-0" :class="dotColorClass"></span>
        <slot>{{ formattedLabel }}</slot>
    </span>
</template>
