<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Mail, Link as LinkIcon, Unlink, Paperclip, CheckCircle2, Clock, Eye, Download, Search, X } from '@lucide/vue';

interface Props {
    emails: any;
    orders: any[];
    statusOptions?: Array<{ value: string; label: string }>;
    documentTypeOptions?: Array<{ value: string; label: string }>;
    filters: { status: string; search: string };
}

const props = defineProps<Props>();

const selectedStatus = ref(props.filters.status || 'needs_review');
const searchQuery = ref(props.filters.search || '');

const filterStatus = (status: string) => {
    selectedStatus.value = status;
    router.get('/emails', { status: status, search: searchQuery.value }, { preserveState: true, replace: true });
};

const handleSearch = () => {
    router.get('/emails', { status: selectedStatus.value, search: searchQuery.value }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    handleSearch();
};

// Email View Modal
const viewingEmail = ref<any>(null);

// Smart Document Type Auto-Detection based on ANK Shipping Carrier Filename Conventions
const autoDetectDocumentType = (filename: string): string => {
    const lower = (filename || '').toLowerCase();
    if (lower.startsWith('bl_') || lower.startsWith('bl-') || lower.includes('lading') || lower.includes('draft')) {
        return 'bill_of_lading';
    }
    if (lower.startsWith('invoice_') || lower.startsWith('inv_') || lower.includes('invoice')) {
        return 'invoice';
    }
    if (lower.includes('dock') || lower.includes('receipt')) {
        return 'dock_receipt';
    }
    if (lower.includes('title') || lower.includes('cert')) {
        return 'title';
    }
    if (lower.includes('telex') || lower.includes('release')) {
        return 'telex_release';
    }
    return 'other';
};

const defaultDocTypes = [
    { value: 'bill_of_lading', label: 'Bill of Lading' },
    { value: 'dock_receipt', label: 'Dock Receipt' },
    { value: 'invoice', label: 'Invoice Document' },
    { value: 'title', label: 'Vehicle Title' },
    { value: 'telex_release', label: 'Telex Release (Printable Text)' },
    { value: 'other', label: 'Other Document' },
];

const availableDocTypeOptions = computed(() => [
    { value: 'skip', label: '-- Do Not Import / Skip File --' },
    ...(props.documentTypeOptions && props.documentTypeOptions.length > 0
        ? props.documentTypeOptions
        : defaultDocTypes),
]);

// Manual Link Email Modal
const linkingEmail = ref<any>(null);
const linkForm = useForm({
    order_id: '',
    status: '',
    attachment_document_types: {} as Record<number, string>,
});

const openLinkModal = (em: any) => {
    linkingEmail.value = em;
    const attachmentDocTypes: Record<number, string> = {};
    if (em.attachments && em.attachments.length > 0) {
        em.attachments.forEach((att: any) => {
            attachmentDocTypes[att.id] = autoDetectDocumentType(att.filename);
        });
    }
    linkForm.order_id = '';
    linkForm.status = '';
    linkForm.attachment_document_types = attachmentDocTypes;
};

const submitLinkEmail = () => {
    if (!linkingEmail.value) return;
    linkForm.post(`/emails/${linkingEmail.value.id}/link`, {
        onSuccess: () => {
            linkingEmail.value = null;
            linkForm.reset();
            showToast.success('Email linked and attachments imported to order successfully!');
        },
    });
};

// Unlink Email Action
const unlinkingEmailConfirm = ref<any>(null);
const unlinkingProcessing = ref(false);

const promptUnlinkEmail = (em: any) => {
    unlinkingEmailConfirm.value = em;
};

const confirmUnlinkEmail = () => {
    if (!unlinkingEmailConfirm.value) return;
    const target = unlinkingEmailConfirm.value;
    unlinkingProcessing.value = true;
    router.post(`/emails/${target.id}/unlink`, {}, {
        onSuccess: () => {
            showToast.success('Email unlinked from order successfully.');
            if (viewingEmail.value?.id === target.id) {
                viewingEmail.value = null;
            }
            unlinkingEmailConfirm.value = null;
        },
        onFinish: () => {
            unlinkingProcessing.value = false;
        },
    });
};

// Clean preview summary generator stripping HTML/DOCTYPE code
const getBodyPreview = (bodyText: string) => {
    if (!bodyText) return 'No preview text available.';
    let clean = bodyText
        .replace(/<!DOCTYPE[^>]*>/gi, '')
        .replace(/<head\b[^>]*>(.*?)<\/head>/gis, '')
        .replace(/<style\b[^>]*>(.*?)<\/style>/gis, '')
        .replace(/<script\b[^>]*>(.*?)<\/script>/gis, '');
    clean = clean.replace(/<[^>]+>/g, ' ');
    clean = clean.replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    clean = clean.replace(/\s+/g, ' ').trim();
    return clean.length > 220 ? clean.substring(0, 220) + '...' : clean;
};

// IMAP Email Sync Trigger
const fetchingImap = ref(false);
const triggerImapFetch = () => {
    fetchingImap.value = true;
    router.post('/emails/fetch', {}, {
        onFinish: () => {
            fetchingImap.value = false;
        },
    });
};
</script>

<template>
    <Head title="Email Inbox - BAMS" />

    <div class="space-y-6">
        <AppPageHeader title="Shipping Email Inbox" description="Ingest incoming order emails from operations@ankshipping.com, extract VINs automatically, and match paperwork to vehicle orders">
            <template #actions>
                <div class="w-full sm:w-auto">
                    <AppButton variant="primary" size="md" :loading="fetchingImap" @click="triggerImapFetch" title="Fetch incoming shipping emails for operations@ankshipping.com" class="w-full sm:w-auto shadow-xs">
                        <Mail class="w-4 h-4" /> Sync Inbox (operations@ankshipping.com)
                    </AppButton>
                </div>
            </template>
        </AppPageHeader>

        <!-- Search & Filter Controls (Mobile First Layout) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
                <button
                    @click="filterStatus('needs_review')"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 shrink-0"
                    :class="selectedStatus === 'needs_review' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                >
                    <Clock class="w-4 h-4" /> Needs Review
                </button>
                <button
                    @click="filterStatus('matched')"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 shrink-0"
                    :class="selectedStatus === 'matched' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                >
                    <CheckCircle2 class="w-4 h-4" /> Auto-Matched
                </button>
                <button
                    @click="filterStatus('archived')"
                    class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2 shrink-0"
                    :class="selectedStatus === 'archived' ? 'bg-slate-700 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                >
                    <Mail class="w-4 h-4" /> Archived
                </button>
            </div>

            <!-- Search Input -->
            <div class="relative w-full sm:w-72">
                <input
                    type="text"
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                    placeholder="Search subject, sender, body..."
                    class="w-full pl-3.5 pr-8 py-2.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-blue-500/20 outline-hidden font-medium"
                />
                <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-white p-1"
                >
                    <X class="w-3.5 h-3.5" />
                </button>
                <button
                    v-else
                    @click="handleSearch"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-white p-1"
                >
                    <Search class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- Emails List -->
        <div v-if="!emails.data || emails.data.length === 0" class="text-center py-16 bg-white dark:bg-slate-800 rounded-2xl border dark:border-slate-700/80 shadow-xs">
            <Mail class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No Emails Found</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                No email messages matched your selected filter category or search query. Click "Sync Inbox" above to fetch incoming emails.
            </p>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="em in emails.data"
                :key="em.id"
                class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/60 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all hover:border-slate-300 dark:hover:border-slate-600"
            >
                <div class="space-y-2 min-w-0 flex-1 cursor-pointer" @click="viewingEmail = em">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-xs font-semibold text-slate-400">From: <strong class="text-slate-800 dark:text-slate-200">{{ em.sender }}</strong></span>
                        <span class="text-xs text-slate-400">&bull; {{ new Date(em.received_at).toLocaleString() }}</span>
                        <AppBadge :status="em.processing_status" size="sm" />
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white hover:text-blue-600 transition-colors leading-snug">{{ em.subject }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl font-normal leading-relaxed">
                        {{ getBodyPreview(em.body) }}
                    </p>
                    <div v-if="em.attachments && em.attachments.length > 0" class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400 font-bold">
                        <Paperclip class="w-3.5 h-3.5 shrink-0" />
                        <span>{{ em.attachments.length }} attachment(s): {{ em.attachments.map((a: any) => a.filename).join(', ') }}</span>
                    </div>
                    <div v-if="em.order" class="text-xs text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                        <CheckCircle2 class="w-3.5 h-3.5 shrink-0" />
                        Linked to Order {{ em.order.order_number }} (VIN: {{ em.order.vin }})
                    </div>
                </div>

                <!-- Touch-friendly Mobile Action Stack -->
                <div class="shrink-0 flex items-center gap-2 pt-2 md:pt-0">
                    <AppButton
                        variant="secondary"
                        size="sm"
                        @click="viewingEmail = em"
                        class="flex-1 md:flex-initial"
                    >
                        <Eye class="w-4 h-4" /> Read
                    </AppButton>

                    <AppButton
                        v-if="em.processing_status === 'needs_review' || !em.order_id"
                        variant="amber"
                        size="sm"
                        @click="openLinkModal(em)"
                        class="flex-1 md:flex-initial"
                    >
                        <LinkIcon class="w-4 h-4" /> Link Order
                    </AppButton>

                    <AppButton
                        v-if="em.order_id || em.order"
                        variant="outline"
                        size="sm"
                        @click="promptUnlinkEmail(em)"
                        class="border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:hover:bg-red-950/40 flex-1 md:flex-initial"
                    >
                        <Unlink class="w-4 h-4" /> Unlink
                    </AppButton>
                </div>
            </div>
        </div>

        <AppPagination :links="emails.links" />
    </div>

    <!-- View Email & Attachments Modal -->
    <AppModal :show="!!viewingEmail" title="Email Communication Details" maxWidth="2xl" @close="viewingEmail = null">
        <div v-if="viewingEmail" class="space-y-5">
            <!-- Email Header Metadata -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 space-y-2 border border-slate-200 dark:border-slate-700/80">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-400 gap-1">
                    <span>Sender: <strong class="text-slate-800 dark:text-slate-100">{{ viewingEmail.sender }}</strong></span>
                    <span>Received: {{ new Date(viewingEmail.received_at).toLocaleString() }}</span>
                </div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-snug">{{ viewingEmail.subject }}</h3>
                <div class="flex items-center gap-2 pt-1 flex-wrap">
                    <AppBadge :status="viewingEmail.processing_status" size="sm" />
                    <span v-if="viewingEmail.order" class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        Linked to Order {{ viewingEmail.order.order_number }}
                    </span>
                </div>
            </div>

            <!-- Full Email Message Body -->
            <div class="space-y-1.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Message Body</h4>
                <div
                    class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 leading-relaxed max-h-96 overflow-y-auto custom-scrollbar prose dark:prose-invert max-w-none"
                    v-html="viewingEmail.body"
                ></div>
            </div>

            <!-- Email Attachments & Download Links -->
            <div v-if="viewingEmail.attachments && viewingEmail.attachments.length > 0" class="space-y-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Attachments ({{ viewingEmail.attachments.length }})</h4>
                <div class="space-y-2">
                    <div
                        v-for="att in viewingEmail.attachments"
                        :key="att.id"
                        class="p-3 rounded-xl bg-blue-50/50 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-800/40 flex items-center justify-between gap-3 text-xs"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <Paperclip class="w-4 h-4 text-blue-600 shrink-0" />
                            <div class="truncate">
                                <span class="font-bold text-slate-900 dark:text-white block truncate">{{ att.filename }}</span>
                                <span class="text-[10px] text-slate-400">Size: {{ Math.round((att.file_size || 102400) / 1024) }} KB</span>
                            </div>
                        </div>
                        <a
                            :href="`/email-attachments/${att.id}/download`"
                            target="_blank"
                            class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1.5 transition-colors shrink-0"
                        >
                            <Download class="w-3.5 h-3.5" /> Download File
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div>
                    <AppButton
                        v-if="viewingEmail.order_id || viewingEmail.order"
                        variant="outline"
                        size="sm"
                        @click="promptUnlinkEmail(viewingEmail)"
                        class="border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:hover:bg-red-950/40"
                    >
                        <Unlink class="w-4 h-4" /> Unlink from Order
                    </AppButton>
                </div>
                <AppButton variant="outline" @click="viewingEmail = null">Close</AppButton>
            </div>
        </div>
    </AppModal>

    <!-- Confirm Unlink Email Modal -->
    <AppModal :show="!!unlinkingEmailConfirm" title="Confirm Unlink Email from Order" @close="unlinkingEmailConfirm = null">
        <div v-if="unlinkingEmailConfirm" class="space-y-4">
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Are you sure you want to unlink email <strong class="text-slate-900 dark:text-white">"{{ unlinkingEmailConfirm.subject }}"</strong> from Order <strong class="text-slate-900 dark:text-white">{{ unlinkingEmailConfirm.order?.order_number || 'Linked Order' }}</strong>?
            </p>
            <div class="p-3 bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-900/40 rounded-xl text-xs text-amber-800 dark:text-amber-300 space-y-1">
                <strong class="font-bold">Note:</strong>
                <p>Unlinking this email will remove it from the vehicle order records and return it to the <strong>Needs Review</strong> status for re-assignment.</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="unlinkingEmailConfirm = null">Cancel</AppButton>
                <AppButton variant="danger" :loading="unlinkingProcessing" @click="confirmUnlinkEmail">
                    Yes, Unlink Email
                </AppButton>
            </div>
        </div>
    </AppModal>

    <!-- Manual Link Modal -->
    <AppModal :show="!!linkingEmail" title="Manually Link Email & Import Documents" maxWidth="xl" @close="linkingEmail = null">
        <form @submit.prevent="submitLinkEmail" class="space-y-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Select the target vehicle order for email: <strong class="text-slate-900 dark:text-white">"{{ linkingEmail?.subject }}"</strong>
            </p>
            <AppFormField label="Select Vehicle Order" required :error="linkForm.errors.order_id">
                <AppSelect v-model="linkForm.order_id" placeholder="Choose order by VIN or Order #...">
                    <option v-for="o in orders" :key="o.id" :value="o.id">
                        {{ o.order_number }} - {{ o.vin }} ({{ o.year }} {{ o.make }} {{ o.model }})
                    </option>
                </AppSelect>
            </AppFormField>

            <AppFormField label="Update Shipment Status (Optional)" :error="linkForm.errors.status">
                <AppSelect
                    v-model="linkForm.status"
                    :options="statusOptions || []"
                    placeholder="Leave unchanged or pick new status..."
                />
            </AppFormField>

            <!-- Attachment Import & Document Type Assignment Section -->
            <div v-if="linkingEmail?.attachments && linkingEmail.attachments.length > 0" class="space-y-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Paperclip class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" />
                        <span>Import Email Attachments as Order Documents ({{ linkingEmail.attachments.length }})</span>
                    </h4>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Document types auto-detected from filename. Select document type to import attachment into Order Documents:
                </p>
                <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                    <div
                        v-for="att in linkingEmail.attachments"
                        :key="att.id"
                        class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                    >
                        <div class="min-w-0 flex-1">
                            <span class="font-bold text-xs text-slate-900 dark:text-white block truncate">{{ att.filename }}</span>
                            <span class="text-[10px] text-slate-400">Size: {{ Math.round((att.file_size || 102400) / 1024) }} KB</span>
                        </div>
                        <div class="w-full sm:w-60 shrink-0">
                            <AppSelect
                                v-model="linkForm.attachment_document_types[att.id]"
                                :options="availableDocTypeOptions"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <AppButton variant="outline" @click="linkingEmail = null">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="linkForm.processing">Link Email & Import Documents</AppButton>
            </div>
        </form>
    </AppModal>
</template>
