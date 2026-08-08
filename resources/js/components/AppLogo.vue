<script setup lang="ts">
interface Props {
    className?: string;
    showSubtext?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

withDefaults(defineProps<Props>(), {
    className: '',
    showSubtext: true,
    size: 'md',
});
</script>

<template>
    <div class="flex items-center gap-2 sm:gap-3 shrink-0" :class="className">
        <img
            src="/logo.jpeg"
            alt="Bubbles Autos Logo"
            class="object-contain rounded-xl shadow-xs transition-transform duration-200 hover:scale-105"
            :class="{
                'h-7 sm:h-8 w-auto': size === 'sm',
                'h-8 sm:h-10 w-auto': size === 'md',
                'h-11 sm:h-14 w-auto': size === 'lg',
            }"
        />
        <div v-if="showSubtext" class="flex flex-col text-left">
            <span class="text-xs sm:text-sm font-extrabold tracking-wide text-slate-900 dark:text-white leading-tight whitespace-nowrap">
                BUBBLES AUTOS
            </span>
            <span class="hidden sm:block text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 whitespace-nowrap">
                Management System
            </span>
        </div>
    </div>
</template>
