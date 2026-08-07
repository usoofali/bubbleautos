<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Mail, Lock, ShieldCheck, KeyRound, AlertCircle } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Bubbles Autos Staff Login',
        description: 'Enter your credentials to access the logistics dashboard',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Staff Login - Bubbles Autos Management System" />

    <div
        v-if="status"
        class="flex items-center gap-2 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-400 text-xs font-medium"
    >
        <ShieldCheck class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
        <span>{{ status }}</span>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5 mt-1"
    >
        <div class="grid gap-5">
            <!-- Email Field -->
            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="email" class="text-slate-700 dark:text-slate-300 font-medium text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <Mail class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                        <span>Staff Email Address</span>
                    </Label>
                </div>
                <div class="relative">
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="staff@bubbleautos.com"
                        class="bg-slate-50 dark:bg-slate-950/70 border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-500 focus:ring-blue-500/20 rounded-xl h-11 transition-all"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Field -->
            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-slate-700 dark:text-slate-300 font-medium text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <Lock class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                        <span>Password</span>
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="••••••••••••"
                    class="bg-slate-50 dark:bg-slate-950/70 border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-500 focus:ring-blue-500/20 rounded-xl h-11 transition-all"
                />
                <InputError :message="errors.password" />
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center justify-between py-1">
                <Label for="remember" class="flex items-center space-x-2.5 cursor-pointer text-slate-700 dark:text-slate-300 text-xs font-medium select-none">
                    <Checkbox id="remember" name="remember" :tabindex="3" class="border-slate-300 dark:border-slate-700 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" />
                    <span>Keep me logged in on this device</span>
                </Label>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                class="mt-2 w-full h-11 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition-all duration-150 flex items-center justify-center gap-2"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <KeyRound v-else class="w-4 h-4" />
                <span>Log in to BAMS Portal</span>
            </Button>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-center gap-2 text-center text-[11px] text-slate-500 dark:text-slate-400">
            <ShieldCheck class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" />
            <span>256-Bit Encrypted Portal &bull; Internal Staff Access Only</span>
        </div>
    </Form>
</template>
