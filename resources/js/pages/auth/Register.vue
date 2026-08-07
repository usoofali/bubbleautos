<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({
    layout: {
        title: 'Registration Disabled',
        description: 'Public registration is disabled for this internal system.',
    },
});
</script>

<template>
    <Head title="Registration Disabled" />

    <div class="text-center py-6 space-y-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Registration Disabled</h2>
        <p class="text-xs text-slate-500 max-w-xs mx-auto">
            Bubble Autos Management System is for internal staff access only. Accounts can only be created by an Administrator.
        </p>
        <div class="pt-2">
            <Link href="/login" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold text-xs">
                Back to Staff Login
            </Link>
        </div>
    </div>
</template>
