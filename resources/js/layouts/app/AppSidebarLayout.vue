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
        <AppContent variant="sidebar" class="overflow-x-hidden flex flex-col min-h-screen relative bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300">
            <!-- Textured Grid & Matrix Background Layer -->
            <div class="absolute inset-0 bg-[radial-gradient(#94a3b8_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-35 dark:opacity-25 pointer-events-none z-0"></div>

            <!-- Ambient Glowing Background Lights -->
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-blue-500/10 dark:bg-blue-600/10 rounded-full blur-[140px] pointer-events-none z-0"></div>
            <div class="absolute top-96 -right-20 w-[400px] h-[400px] bg-indigo-500/10 dark:bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

            <!-- Subtle Brand Watermark Text & Graphic -->
            <div class="absolute inset-0 flex items-center justify-center overflow-hidden pointer-events-none select-none z-0">
                <div class="w-[600px] h-[600px] rounded-full border border-blue-500/10 dark:border-blue-400/10 flex items-center justify-center relative">
                    <img src="/logo.jpeg" alt="" class="w-[350px] h-[350px] object-contain rounded-full opacity-[0.025] dark:opacity-[0.05] grayscale blur-[1px]" />
                </div>
                <div class="absolute text-[100px] sm:text-[150px] font-black uppercase tracking-[0.25em] text-slate-900/[0.025] dark:text-white/[0.035] whitespace-nowrap -rotate-6 select-none">
                    BUBBLES AUTOS
                </div>
            </div>

            <!-- Content Area -->
            <div class="relative z-10 flex flex-col flex-1">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <div class="flex-1 p-6 md:p-8 lg:p-10 max-w-[1600px] w-full">
                    <slot />
                </div>
            </div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
