<script setup lang="ts">
import AppModal from './AppModal.vue';
import AppButton from './AppButton.vue';
import { AlertTriangle } from '@lucide/vue';

interface Props {
    show: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'amber';
    loading?: boolean;
}

withDefaults(defineProps<Props>(), {
    show: false,
    title: 'Confirm Action',
    message: 'Are you sure you want to proceed? This action cannot be undone.',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits(['confirm', 'close']);

const onConfirm = () => {
    emit('confirm');
};

const onClose = () => {
    emit('close');
};
</script>

<template>
    <AppModal :show="show" max-width="sm" @close="onClose">
        <div class="flex flex-col items-center text-center">
            <div
                class="flex items-center justify-center w-12 h-12 rounded-full mb-4"
                :class="variant === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'"
            >
                <AlertTriangle class="w-6 h-6" />
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">{{ title }}</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ message }}</p>
            <div class="flex items-center justify-center gap-3 w-full">
                <AppButton variant="outline" class="w-full" :disabled="loading" @click="onClose">
                    {{ cancelText }}
                </AppButton>
                <AppButton :variant="variant" class="w-full" :loading="loading" @click="onConfirm">
                    {{ confirmText }}
                </AppButton>
            </div>
        </div>
    </AppModal>
</template>
