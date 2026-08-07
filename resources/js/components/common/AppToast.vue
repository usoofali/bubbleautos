<script lang="ts">
import { toast } from 'vue-sonner';

export const showToast = {
    success: (message: string) => {
        toast.success(message, {
            duration: 4000,
        });
    },
    error: (message: string) => {
        toast.error(message, {
            duration: 5000,
        });
    },
    info: (message: string) => {
        toast.info(message, {
            duration: 4000,
        });
    },
    warning: (message: string) => {
        toast.warning(message, {
            duration: 4000,
        });
    },
};

export default {
    name: 'AppToast',
};
</script>

<template>
    <!-- Vue Sonner Toast mount helper -->
</template>
