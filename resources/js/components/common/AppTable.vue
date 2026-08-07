<script setup lang="ts">
import AppEmptyState from './AppEmptyState.vue';
import AppLoading from './AppLoading.vue';

interface Column {
    key: string;
    label: string;
    align?: 'left' | 'center' | 'right';
}

interface Props {
    columns?: Column[];
    items?: any[];
    loading?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
}

withDefaults(defineProps<Props>(), {
    loading: false,
    columns: () => [],
    items: () => [],
    emptyTitle: 'No Records Found',
});
</script>

<template>
    <div class="w-full overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-700/60 bg-white dark:bg-slate-800 shadow-sm">
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-700/60">
                <tr>
                    <slot name="header">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-6 py-3.5"
                            :class="{
                                'text-center': col.align === 'center',
                                'text-right': col.align === 'right',
                            }"
                        >
                            {{ col.label }}
                        </th>
                    </slot>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                <template v-if="loading">
                    <tr>
                        <td :colspan="columns.length || 10">
                            <AppLoading title="Fetching table data..." />
                        </td>
                    </tr>
                </template>
                <template v-else-if="!items || items.length === 0">
                    <tr>
                        <td :colspan="columns.length || 10">
                            <AppEmptyState :title="emptyTitle" :description="emptyDescription" />
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <slot name="rows" :items="items">
                        <tr v-for="(item, idx) in items" :key="idx" class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="px-6 py-4"
                                :class="{
                                    'text-center': col.align === 'center',
                                    'text-right': col.align === 'right',
                                }"
                            >
                                {{ item[col.key] }}
                            </td>
                        </tr>
                    </slot>
                </template>
            </tbody>
        </table>
    </div>
</template>
