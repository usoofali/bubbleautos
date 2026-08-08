<script setup lang="ts">
import { Sun, Moon } from '@lucide/vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance, resolvedAppearance } = useAppearance();

function toggleNextTheme() {
    if (appearance.value === 'system') {
        updateAppearance('light');
    } else if (appearance.value === 'light') {
        updateAppearance('dark');
    } else {
        updateAppearance('system');
    }
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex items-center gap-3 ml-auto">
            <!-- Theme Toggle Switcher -->
            <button
                @click="toggleNextTheme()"
                type="button"
                class="inline-flex items-center justify-center gap-1.5 p-2 sm:px-3 sm:py-1.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 active:scale-95 transition-all shadow-xs shrink-0 cursor-pointer"
                :title="`Current theme preference: ${appearance} (${resolvedAppearance}). Click to toggle.`"
            >
                <Sun v-if="resolvedAppearance === 'light'" class="w-4 h-4 sm:w-3.5 sm:h-3.5 text-amber-500 shrink-0" />
                <Moon v-else class="w-4 h-4 sm:w-3.5 sm:h-3.5 text-blue-400 shrink-0" />
                <span class="hidden sm:inline capitalize">{{ appearance }}</span>
            </button>
        </div>
    </header>
</template>
