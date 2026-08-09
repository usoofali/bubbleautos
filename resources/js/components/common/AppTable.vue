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
    <div class="w-full overflow-x-auto rounded-3xl border border-slate-200/80 dark:border-slate-800/80 bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl shadow-xs dark:shadow-xl dark:shadow-slate-950/40 transition-colors">
        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
            <thead class="bg-slate-50/80 dark:bg-slate-950/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-200/80 dark:border-slate-800/80 whitespace-nowrap">
                <tr>
                    <slot name="header">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="px-4 sm:px-6 py-4"
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
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
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
                        <tr v-for="(item, idx) in items" :key="idx" class="hover:bg-blue-50/40 dark:hover:bg-blue-950/30 transition-colors">
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
