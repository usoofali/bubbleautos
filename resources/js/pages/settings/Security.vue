<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';
import { ShieldAlert, ShieldCheck } from '@lucide/vue';

type Props = {
    passwordRules: string;
    isAdmin?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    isAdmin: false,
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Security Settings - BAMS" />

    <h1 class="sr-only">Security Settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Account Security & Password Management"
            description="System password policy and security credentials"
        />

        <!-- Non-Admin Staff Notice -->
        <div
            v-if="!props.isAdmin"
            class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 flex items-start gap-4"
        >
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold shrink-0 mt-0.5">
                <ShieldAlert class="w-5 h-5" />
            </div>
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                    Password Changes Managed by Administrator
                </h4>
                <p class="text-xs text-amber-800/90 dark:text-amber-300/80 leading-relaxed">
                    Staff members cannot change their passwords directly. All account password updates and resets are exclusively managed by a System Administrator via Staff Management.
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 pt-1">
                    If you require a password reset or credential change, please contact your System Administrator.
                </p>
            </div>
        </div>

        <!-- Admin Password Change Form -->
        <div v-else class="space-y-6">
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 flex items-center gap-3 text-xs text-emerald-800 dark:text-emerald-300">
                <ShieldCheck class="w-5 h-5 text-emerald-600 shrink-0" />
                <span>As a System Administrator, you have full privileges to update your password credentials below.</span>
            </div>

            <Form
                v-bind="SecurityController.update.form()"
                :options="{
                    preserveScroll: true,
                }"
                reset-on-success
                :reset-on-error="[
                    'password',
                    'password_confirmation',
                    'current_password',
                ]"
                class="space-y-6 max-w-xl"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="current_password">Current Password</Label>
                    <PasswordInput
                        id="current_password"
                        name="current_password"
                        class="mt-1 block w-full"
                        autocomplete="current-password"
                        placeholder="Current password"
                    />
                    <InputError :message="errors.current_password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">New Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        placeholder="New password"
                        :passwordrules="props.passwordRules"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm New Password</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                        :passwordrules="props.passwordRules"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <Button
                        :disabled="processing"
                        data-test="update-password-button"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                    >
                        Save Password Updates
                    </Button>
                </div>
            </Form>
        </div>
    </div>
</template>
