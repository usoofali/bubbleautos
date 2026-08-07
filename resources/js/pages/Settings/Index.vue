<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Settings, Globe, Briefcase, Mail } from '@lucide/vue';

interface Props {
    general: Record<string, any>;
    business: Record<string, any>;
    website: Record<string, any>;
    email: Record<string, any>;
}

const props = defineProps<Props>();

const activeTab = ref<'general' | 'business' | 'website' | 'email'>('general');

// Options for Shipping Line & Destination Port
const shippingLineOptions = [
    { value: 'Sallaum Lines', label: 'Sallaum Lines' },
    { value: 'Grimaldi Lines', label: 'Grimaldi Lines' },
    { value: 'MSC Line', label: 'MSC Line' },
];

const destinationPortOptions = [
    { value: 'Lagos, Nigeria', label: 'Lagos, Nigeria' },
    { value: 'Cotonou, Benin', label: 'Cotonou, Benin' },
];

// General Form
const generalForm = useForm({
    settings: {
        company_name: props.general.company_name || 'Bubbles Autos',
        company_logo: props.general.company_logo || '/images/logo.png',
        currency_symbol: props.general.currency_symbol || '$',
        time_zone: props.general.time_zone || 'UTC',
    },
});

const submitGeneral = () => {
    generalForm.post('/settings/general', {
        onSuccess: () => showToast.success('General settings saved.'),
    });
};

// Business Form
const businessForm = useForm({
    settings: {
        order_prefix: props.business.order_prefix || 'BA-',
        invoice_prefix: props.business.invoice_prefix || 'INV-',
        default_destination: props.business.default_destination || 'Lagos, Nigeria',
        default_shipping_line: props.business.default_shipping_line || 'Grimaldi Lines',
    },
});

const submitBusiness = () => {
    businessForm.post('/settings/business', {
        onSuccess: () => showToast.success('Business settings saved.'),
    });
};

// Website CMS Form
const websiteForm = useForm({
    settings: {
        hero_title: props.website.hero_title || 'Global Vehicle Shipment & Management System',
        hero_subtitle: props.website.hero_subtitle || 'Internal management portal for Bubble Autos staff.',
        contact_phone: props.website.contact_phone || '+1 (800) 555-BUBBLE',
        contact_email: props.website.contact_email || 'contact@bubbleautos.com',
        contact_address: props.website.contact_address || '100 Shipping Way, Houston, TX 77001',
    },
});

const submitWebsite = () => {
    websiteForm.post('/settings/website', {
        onSuccess: () => showToast.success('Public landing page CMS updated.'),
    });
};

// Email & IMAP Configuration Form
const emailForm = useForm({
    settings: {
        email_account: props.email.email_account || 'operations@ankshipping.com',
        imap_host: props.email.imap_host || 'imap.gmail.com',
        imap_port: props.email.imap_port || '993',
        imap_encryption: props.email.imap_encryption || 'ssl',
        imap_username: props.email.imap_username || 'operations@ankshipping.com',
        imap_password: props.email.imap_password || '',
    },
});

const submitEmail = () => {
    emailForm.post('/settings/email', {
        onSuccess: () => showToast.success('Email & IMAP carrier settings saved successfully.'),
    });
};
</script>

<template>
    <Head title="System Settings - BAMS" />

    <div class="space-y-6">
        <AppPageHeader title="System & Website Settings" description="Configure application preferences, order prefixes, currency formats, public website CMS, and incoming email credentials" />

        <!-- Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-2 flex-wrap">
            <button
                @click="activeTab = 'general'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'general' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
            >
                <Settings class="w-4 h-4" /> General Settings
            </button>
            <button
                @click="activeTab = 'business'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'business' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
            >
                <Briefcase class="w-4 h-4" /> Business & Order Prefixes
            </button>
            <button
                @click="activeTab = 'email'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'email' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
            >
                <Mail class="w-4 h-4" /> Shipping Email Integration
            </button>
            <button
                @click="activeTab = 'website'"
                class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all cursor-pointer flex items-center gap-2"
                :class="activeTab === 'website' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
            >
                <Globe class="w-4 h-4" /> Landing Page CMS
            </button>
        </div>

        <!-- General Settings Tab -->
        <div v-if="activeTab === 'general'">
            <AppCard title="General System Configuration">
                <form @submit.prevent="submitGeneral" class="space-y-4 max-w-xl">
                    <AppFormField label="Company Name">
                        <AppInput v-model="generalForm.settings.company_name" />
                    </AppFormField>
                    <AppFormField label="Currency Symbol">
                        <AppInput v-model="generalForm.settings.currency_symbol" placeholder="$" />
                    </AppFormField>
                    <AppFormField label="System Time Zone">
                        <AppInput v-model="generalForm.settings.time_zone" placeholder="UTC" />
                    </AppFormField>
                    <div class="flex justify-end pt-3">
                        <AppButton type="submit" variant="primary" :loading="generalForm.processing">Save General Settings</AppButton>
                    </div>
                </form>
            </AppCard>
        </div>

        <!-- Business Settings Tab -->
        <div v-if="activeTab === 'business'">
            <AppCard title="Business Prefixes & Defaults">
                <form @submit.prevent="submitBusiness" class="space-y-4 max-w-xl">
                    <AppFormField label="Bubble Order Prefix (e.g. BA- creates BA-00001)">
                        <AppInput v-model="businessForm.settings.order_prefix" placeholder="BA-" />
                    </AppFormField>
                    <AppFormField label="Invoice Prefix (e.g. INV-)">
                        <AppInput v-model="businessForm.settings.invoice_prefix" placeholder="INV-" />
                    </AppFormField>

                    <!-- Select for Default Destination Port -->
                    <AppFormField label="Default Destination Port">
                        <AppSelect
                            v-model="businessForm.settings.default_destination"
                            :options="destinationPortOptions"
                            placeholder="Select Default Destination Port..."
                        />
                    </AppFormField>

                    <!-- Select for Default Shipping Line -->
                    <AppFormField label="Default Shipping Line">
                        <AppSelect
                            v-model="businessForm.settings.default_shipping_line"
                            :options="shippingLineOptions"
                            placeholder="Select Default Shipping Line..."
                        />
                    </AppFormField>

                    <div class="flex justify-end pt-3">
                        <AppButton type="submit" variant="primary" :loading="businessForm.processing">Save Business Prefixes</AppButton>
                    </div>
                </form>
            </AppCard>
        </div>

        <!-- Shipping Email Integration Tab -->
        <div v-if="activeTab === 'email'">
            <AppCard title="Incoming Shipping Mail Account & IMAP Settings" description="Configure Gmail or IMAP server credentials to fetch and parse shipping emails from operations@ankshipping.com">
                <form @submit.prevent="submitEmail" class="space-y-4 max-w-xl">
                    <AppFormField label="Shipping Carrier Email Account">
                        <AppInput v-model="emailForm.settings.email_account" placeholder="operations@ankshipping.com" />
                    </AppFormField>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AppFormField label="IMAP Server Host">
                            <AppInput v-model="emailForm.settings.imap_host" placeholder="imap.gmail.com" />
                        </AppFormField>
                        <AppFormField label="IMAP Port">
                            <AppInput v-model="emailForm.settings.imap_port" placeholder="993" />
                        </AppFormField>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <AppFormField label="Encryption Method">
                            <AppSelect
                                v-model="emailForm.settings.imap_encryption"
                                :options="[
                                    { value: 'ssl', label: 'SSL (Port 993)' },
                                    { value: 'tls', label: 'STARTTLS (Port 143)' },
                                    { value: 'none', label: 'None (Plaintext)' },
                                ]"
                            />
                        </AppFormField>

                        <AppFormField label="IMAP Username">
                            <AppInput v-model="emailForm.settings.imap_username" placeholder="operations@ankshipping.com" />
                        </AppFormField>
                    </div>

                    <AppFormField label="Gmail App Password / Account Password">
                        <AppInput type="password" v-model="emailForm.settings.imap_password" placeholder="•••• •••• •••• ••••" />
                    </AppFormField>

                    <div class="p-3 bg-blue-50/60 dark:bg-blue-950/30 rounded-xl border border-blue-200/60 dark:border-blue-900/40 text-xs text-blue-800 dark:text-blue-300 space-y-1">
                        <strong class="font-bold">Gmail Setup Tip:</strong>
                        <p>If using Gmail to manage <code>operations@ankshipping.com</code>, generate a 16-character App Password under your Google Account Security settings and paste it above.</p>
                    </div>

                    <div class="flex justify-end pt-3">
                        <AppButton type="submit" variant="primary" :loading="emailForm.processing" class="w-full sm:w-auto">Save Email Credentials</AppButton>
                    </div>
                </form>
            </AppCard>
        </div>

        <!-- Website CMS Tab -->
        <div v-if="activeTab === 'website'">
            <AppCard title="Public Landing Page Content (CMS)">
                <form @submit.prevent="submitWebsite" class="space-y-4 max-w-xl">
                    <AppFormField label="Hero Headline Title">
                        <AppInput v-model="websiteForm.settings.hero_title" />
                    </AppFormField>
                    <AppFormField label="Hero Subtitle Paragraph">
                        <AppTextarea v-model="websiteForm.settings.hero_subtitle" :rows="3" />
                    </AppFormField>
                    <AppFormField label="Contact Phone Number">
                        <AppInput v-model="websiteForm.settings.contact_phone" />
                    </AppFormField>
                    <AppFormField label="Contact Email Address">
                        <AppInput v-model="websiteForm.settings.contact_email" />
                    </AppFormField>
                    <AppFormField label="Office Address">
                        <AppInput v-model="websiteForm.settings.contact_address" />
                    </AppFormField>
                    <div class="flex justify-end pt-3">
                        <AppButton type="submit" variant="primary" :loading="websiteForm.processing">Update Public Website CMS</AppButton>
                    </div>
                </form>
            </AppCard>
        </div>
    </div>
</template>
