<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden flex flex-col min-h-screen">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex-1 p-6 md:p-8 lg:p-10 max-w-[1600px] w-full">
                <slot />
            </div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
