<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Sun, Moon, Monitor } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import { useAppearance } from '@/composables/useAppearance';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

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
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 p-4 sm:p-6 md:p-10 transition-colors duration-300 selection:bg-blue-600 selection:text-white"
    >
        <!-- Theme Toggle Floating Switch -->
        <div class="absolute top-4 right-4 sm:top-5 sm:right-5 z-20">
            <button
                @click="toggleNextTheme()"
                type="button"
                class="inline-flex items-center justify-center gap-2 p-2.5 sm:px-3 sm:py-1.5 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200/90 dark:border-slate-800/90 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-95 transition-all shadow-xs"
                :title="`Current theme: ${appearance} (${resolvedAppearance}). Click to toggle.`"
            >
                <Sun v-if="resolvedAppearance === 'light'" class="w-4 h-4 sm:w-3.5 sm:h-3.5 text-amber-500 shrink-0" />
                <Moon v-else class="w-4 h-4 sm:w-3.5 sm:h-3.5 text-blue-400 shrink-0" />
                <span class="hidden sm:inline capitalize">{{ appearance }}</span>
            </button>
        </div>

        <!-- Textured Grid Pattern Layer -->
        <div class="absolute inset-0 bg-[radial-gradient(#94a3b8_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-40 dark:opacity-30 pointer-events-none"></div>

        <!-- Ambient Glowing Background Elements -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-500/10 dark:bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#cbd5e120_1px,transparent_1px),linear-gradient(to_bottom,#cbd5e120_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#1e293b20_1px,transparent_1px),linear-gradient(to_bottom,#1e293b20_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>

        <!-- Subtle Watermark Background Graphics -->
        <div class="absolute inset-0 flex items-center justify-center overflow-hidden pointer-events-none select-none">
            <div class="w-[500px] h-[500px] rounded-full border border-blue-500/10 dark:border-blue-400/10 flex items-center justify-center relative">
                <img src="/logo.jpeg" alt="" class="w-72 h-72 object-contain rounded-full opacity-5 dark:opacity-10 grayscale blur-[1px]" />
            </div>
            <div class="absolute text-[80px] sm:text-[110px] font-black uppercase tracking-[0.2em] text-slate-900/[0.03] dark:text-white/[0.04] whitespace-nowrap -rotate-12">
                BUBBLES AUTOS
            </div>
        </div>

        <div class="relative z-10 w-full max-w-md">
            <div class="flex flex-col gap-6 bg-white/90 dark:bg-slate-900/80 backdrop-blur-xl p-6 sm:p-8 md:p-10 rounded-2xl sm:rounded-3xl shadow-xl dark:shadow-2xl dark:shadow-blue-950/40 border border-slate-200/80 dark:border-slate-800/80 transition-all duration-300">
                <div class="flex flex-col items-center gap-4 text-center">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 group focus:outline-hidden"
                    >
                        <div class="p-2.5 rounded-2xl bg-slate-100/80 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 group-hover:border-blue-500/50 transition-colors shadow-inner">
                            <AppLogo size="lg" :show-subtext="true" />
                        </div>
                    </Link>
                    
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/40 text-blue-700 dark:text-blue-400 text-[11px] font-semibold uppercase tracking-wider mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400 animate-pulse"></span>
                        <span>Secure Operations Access</span>
                    </div>

                    <div class="space-y-1.5 mt-1">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ title }}</h1>
                        <p class="text-xs text-slate-600 dark:text-slate-400 max-w-xs mx-auto leading-relaxed">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
