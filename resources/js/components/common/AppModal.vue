<script setup lang="ts">
import { watch, onMounted, onUnmounted, ref } from 'vue';
import { X } from '@lucide/vue';

interface Props {
    show: boolean;
    title?: string;
    description?: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl';
    closeOnOutsideClick?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    maxWidth: 'md',
    closeOnOutsideClick: false,
});

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) {
        closeModal();
    }
};

const isLocked = ref(false);

const checkAndReleaseScrollLock = () => {
    // Check if any active modal backdrop remains in the DOM
    const activeModals = document.querySelectorAll('.app-modal-backdrop');
    if (activeModals.length <= 1) {
        document.body.style.overflow = '';
    }
};

const updateScrollLock = (shouldLock: boolean) => {
    if (shouldLock) {
        isLocked.value = true;
        document.body.style.overflow = 'hidden';
    } else {
        isLocked.value = false;
        checkAndReleaseScrollLock();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
    if (typeof window !== 'undefined') {
        window.addEventListener('afterprint', checkAndReleaseScrollLock);
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
    if (typeof window !== 'undefined') {
        window.removeEventListener('afterprint', checkAndReleaseScrollLock);
    }
    if (isLocked.value) {
        isLocked.value = false;
        checkAndReleaseScrollLock();
    }
});

watch(
    () => props.show,
    (val) => {
        updateScrollLock(val);
    },
    { immediate: true }
);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
            @after-leave="checkAndReleaseScrollLock"
        >
            <div
                v-if="show"
                class="app-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto bg-slate-900/60 backdrop-blur-sm"
                @click.self="closeOnOutsideClick ? closeModal() : null"
            >
                <div
                    class="w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all"
                    :class="{
                        'max-w-sm': maxWidth === 'sm',
                        'max-w-md': maxWidth === 'md',
                        'max-w-lg': maxWidth === 'lg',
                        'max-w-xl': maxWidth === 'xl',
                        'max-w-2xl': maxWidth === '2xl',
                        'max-w-3xl': maxWidth === '3xl',
                    }"
                >
                    <!-- Header -->
                    <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700/80">
                        <slot name="header">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ title }}</h3>
                                <p v-if="description" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ description }}</p>
                            </div>
                        </slot>
                        <button
                            @click="closeModal"
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 dark:hover:text-slate-200 transition-colors"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 max-h-[80vh] overflow-y-auto">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700/80">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
