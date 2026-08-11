<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import AppDatePicker from '@/components/common/AppDatePicker.vue';
import FileUploader from '@/components/common/FileUploader.vue';
import { showToast } from '@/components/common/AppToast.vue';
import {
    Car,
    User,
    DollarSign,
    FileText,
    Mail,
    MessageSquare,
    Clock,
    Plus,
    Trash2,
    SquarePen,
    Download,
    Copy,
    Check,
    Truck,
    Calendar,
    Sparkles,
    Image as ImageIcon,
    RotateCw,
    Printer,
    FileCheck,
    Search,
    ShieldCheck,
    Paperclip,
    Unlink,
    ExternalLink,
    Send,
    Eye,
} from '@lucide/vue';

interface Order {
    id: number;
    order_number: string;
    vin: string;
    customer_id: number;
    make?: string;
    model?: string;
    year?: number;
    color?: string;
    shipping_line?: string;
    destination?: string;
    status: string;
    expected_arrival?: string;
    created_at?: string;
    pictures?: string[];
    customer?: any;
    invoice?: any;
    documents?: any[];
    emails?: any[];
    notes?: any[];
    timeline_events?: any[];
}

const props = defineProps<{
    order: Order;
    customers?: any[];
    statusOptions: Array<{ value: string; label: string }>;
    companySettings?: {
        name?: string;
        logo?: string;
        address?: string;
        email?: string;
        phone?: string;
        currency_symbol?: string;
        currency_code?: string;
    };
    invoiceItemTemplates?: Array<{ id: number; description: string; default_amount: number | string }>;
}>();

const page = usePage();
const currencySymbol = computed(() => props.companySettings?.currency_symbol || (page.props as any).currencySymbol || '$');
const currencyCode = computed(() => props.companySettings?.currency_code || (page.props as any).currencyCode || 'USD');

const activeTab = ref<'overview' | 'financials' | 'documents' | 'emails' | 'notes' | 'timeline'>('overview');

// Customer dropdown options for edit order modal
const customerOptions = computed(() => {
    return (props.customers || []).map((c) => ({
        value: c.id,
        label: `${c.name} (${c.phone})`,
    }));
});

// Copy VIN
const copiedVin = ref(false);
const copyVin = () => {
    navigator.clipboard.writeText(props.order.vin);
    copiedVin.value = true;
    showToast.success('VIN copied to clipboard!');
    setTimeout(() => (copiedVin.value = false), 2000);
};

// Date Formatter (Date only, no time)
const formatDateOnly = (val: string | null | undefined) => {
    if (!val) return 'TBD';
    const cleanDate = val.split('T')[0].split(' ')[0];
    const parts = cleanDate.split('-');
    if (parts.length === 3) {
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);
        const d = new Date(year, month, day);
        if (!isNaN(d.getTime())) {
            return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }
    }
    return cleanDate;
};

// Vehicle API Sync Service
const syncingApi = ref(false);
const syncVehicleApi = () => {
    syncingApi.value = true;
    showToast.info('Connecting to ANK Shipping VIN API service...');
    router.post(`/orders/${props.order.id}/sync-vehicle-api`, {}, {
        onFinish: () => {
            syncingApi.value = false;
        },
    });
};

// Image Lightbox Modal
const activeImageIndex = ref<number | null>(null);

// Edit Order Modal & Live VIN Lookup Service
const showEditOrderModal = ref(false);
const lookingUpVin = ref(false);
const editOrderForm = useForm({
    customer_id: props.order.customer_id || '',
    vin: props.order.vin || '',
    make: props.order.make || '',
    model: props.order.model || '',
    year: props.order.year || '',
    color: props.order.color || '',
    shipping_line: props.order.shipping_line || '',
    destination: props.order.destination || '',
    expected_arrival: props.order.expected_arrival || '',
});

const openEditOrderModal = () => {
    editOrderForm.customer_id = props.order.customer_id || '';
    editOrderForm.vin = props.order.vin || '';
    editOrderForm.make = props.order.make || '';
    editOrderForm.model = props.order.model || '';
    editOrderForm.year = props.order.year || '';
    editOrderForm.color = props.order.color || '';
    editOrderForm.shipping_line = props.order.shipping_line || '';
    editOrderForm.destination = props.order.destination || '';
    editOrderForm.expected_arrival = props.order.expected_arrival || '';
    showEditOrderModal.value = true;
};

const lookupVinInEditModal = async () => {
    if (!editOrderForm.vin || editOrderForm.vin.length !== 17) {
        showToast.error('Please enter a valid 17-digit VIN.');
        return;
    }

    lookingUpVin.value = true;
    try {
        const response = await fetch(`/api/vehicles/${encodeURIComponent(editOrderForm.vin)}/lookup`);
        const data = await response.json();
        if (data.success) {
            if (data.make) editOrderForm.make = data.make;
            if (data.model) editOrderForm.model = data.model;
            if (data.year) editOrderForm.year = data.year;
            showToast.success(`VIN Resolved: ${data.year || ''} ${data.make || ''} ${data.model || ''}`);
        } else {
            showToast.info(data.message || 'No vehicle specs found on ANK Shipping server for this VIN.');
        }
    } catch (e) {
        showToast.error('Failed to connect to VIN Lookup service.');
    } finally {
        lookingUpVin.value = false;
    }
};

const submitEditOrder = () => {
    editOrderForm.patch(`/orders/${props.order.id}`, {
        onSuccess: () => {
            showEditOrderModal.value = false;
            showToast.success('Order details updated successfully.');
        },
    });
};

// Tracking & Delivery ETA Update Form
const showTrackingModal = ref(false);
const trackingForm = useForm({
    expected_arrival: props.order.expected_arrival || '',
    status: '',
    notes: '',
});

const openTrackingModal = () => {
    trackingForm.expected_arrival = props.order.expected_arrival || '';
    trackingForm.status = '';
    trackingForm.notes = '';
    showTrackingModal.value = true;
};

const submitTrackingUpdate = () => {
    trackingForm.patch(`/orders/${props.order.id}/tracking`, {
        onSuccess: () => {
            showTrackingModal.value = false;
            showToast.success('Tracking information and expected arrival saved successfully.');
        },
    });
};

// Line Item Form (Description + Amount only, with Edit capability)
const showItemModal = ref(false);
const editingItem = ref<any>(null);
const selectedTemplateId = ref<string | number>('');
const itemForm = useForm({
    description: '',
    amount: '',
});

const onPresetTemplateSelected = (val: any) => {
    selectedTemplateId.value = val;
    if (!val) return;
    const tmpl = props.invoiceItemTemplates?.find(t => t.id === Number(val));
    if (tmpl) {
        itemForm.description = tmpl.description;
        itemForm.amount = String(tmpl.default_amount);
    }
};

const openAddItemModal = () => {
    editingItem.value = null;
    selectedTemplateId.value = '';
    itemForm.reset();
    showItemModal.value = true;
};

const openEditItemModal = (item: any) => {
    editingItem.value = item;
    selectedTemplateId.value = '';
    itemForm.description = item.description;
    itemForm.amount = item.amount;
    showItemModal.value = true;
};

const submitItemForm = () => {
    if (editingItem.value) {
        itemForm.patch(`/invoice-items/${editingItem.value.id}`, {
            onSuccess: () => {
                showItemModal.value = false;
                editingItem.value = null;
                itemForm.reset();
                showToast.success('Invoice item updated.');
            },
        });
    } else {
        itemForm.post(`/orders/${props.order.invoice?.id}/invoice/items`, {
            onSuccess: () => {
                showItemModal.value = false;
                itemForm.reset();
                showToast.success('Line item added to invoice.');
            },
        });
    }
};

// Unlink Email Action
const unlinkingEmailConfirm = ref<any>(null);
const unlinkingEmailProcessing = ref(false);
const promptUnlinkEmailFromOrder = (em: any) => {
    unlinkingEmailConfirm.value = em;
};
const confirmUnlinkEmailFromOrder = () => {
    if (!unlinkingEmailConfirm.value) return;
    const target = unlinkingEmailConfirm.value;
    unlinkingEmailProcessing.value = true;
    router.post(`/emails/${target.id}/unlink`, {}, {
        onSuccess: () => {
            showToast.success('Email unlinked from order successfully.');
            unlinkingEmailConfirm.value = null;
        },
        onFinish: () => {
            unlinkingEmailProcessing.value = false;
        },
    });
};

// Remove Line Item Confirm Modal
const deletingItem = ref<any>(null);
const confirmRemoveItem = () => {
    if (!deletingItem.value) return;
    router.delete(`/invoice-items/${deletingItem.value.id}`, {
        onSuccess: () => {
            deletingItem.value = null;
            showToast.success('Line item removed.');
        },
    });
};

// Payment Form
const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount: props.order.invoice?.balance || 0,
    payment_date: new Date().toISOString().split('T')[0],
    method: 'bank_transfer',
    reference: '',
    notes: '',
});

const submitPayment = () => {
    paymentForm.post(`/orders/${props.order.invoice?.id}/payments`, {
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
            showToast.success('Payment recorded successfully!');
        },
    });
};

// Delete Payment Confirm Modal
const deletingPayment = ref<any>(null);
const confirmDeletePayment = () => {
    if (!deletingPayment.value) return;
    router.delete(`/payments/${deletingPayment.value.id}`, {
        onSuccess: () => {
            deletingPayment.value = null;
            showToast.success('Payment entry deleted.');
        },
    });
};

// Document Upload Form & Simplified Document Type Selection
const showDocumentModal = ref(false);
const documentForm = useForm({
    document_type: 'bill_of_lading',
    content: '',
    file: null as File | null,
    status: '',
});

const handleFileSelected = (file: File | null) => {
    documentForm.file = file;
};

const submitDocumentUpload = () => {
    documentForm.post(`/orders/${props.order.id}/documents`, {
        onSuccess: () => {
            showDocumentModal.value = false;
            documentForm.reset();
            showToast.success('Document uploaded and saved successfully!');
        },
        onError: (errors) => {
            console.error('Document Upload Errors:', errors);
            showToast.error('Failed to attach document. Please check required fields.');
        },
    });
};



// Printable Telex Release Viewer Modal
const printingTelexDoc = ref<any>(null);
const printTelexRelease = () => {
    window.print();
};

// WhatsApp Integration for Documents & Telex Release
const formatPhoneForWhatsapp = (phone?: string) => {
    if (!phone) return '';
    let cleaned = phone.replace(/\D/g, '');
    if (cleaned.startsWith('0') && cleaned.length === 11) {
        cleaned = '234' + cleaned.substring(1);
    }
    return cleaned;
};

const shareTelexViaWhatsapp = (doc: any) => {
    const rawPhone = props.order.customer?.phone || '';
    const phone = formatPhoneForWhatsapp(rawPhone);
    const vehicle = [props.order.year, props.order.make, props.order.model].filter(Boolean).join(' ') || 'Vehicle';
    
    let text = `*BUBBLES AUTOS - TELEX RELEASE*\n\n`;
    text += `📋 *Order #:* ${props.order.order_number}\n`;
    text += `🚗 *Vehicle:* ${vehicle}\n`;
    text += `🔑 *VIN:* ${props.order.vin}\n`;
    text += `👤 *Customer:* ${props.order.customer?.name || 'Valued Customer'}\n\n`;
    text += `📄 *Telex Release Note:*\n${doc.content || 'Telex release confirmed for this shipment.'}\n\n`;
    text += `Thank you for choosing Bubbles Autos!`;

    const encodedText = encodeURIComponent(text);
    const url = phone ? `https://wa.me/${phone}?text=${encodedText}` : `https://wa.me/?text=${encodedText}`;
    window.open(url, '_blank');
};

const shareDocumentViaWhatsapp = async (doc: any) => {
    const rawPhone = props.order.customer?.phone || '';
    const phone = formatPhoneForWhatsapp(rawPhone);
    const vehicle = [props.order.year, props.order.make, props.order.model].filter(Boolean).join(' ') || 'Vehicle';
    const docLabel = getDocTypeLabel(doc.document_type);
    
    const docUrl = window.location.origin + `/documents/${doc.id}/download`;

    let text = `*BUBBLES AUTOS - ${docLabel.toUpperCase()}*\n\n`;
    text += `📋 *Order #:* ${props.order.order_number}\n`;
    text += `🚗 *Vehicle:* ${vehicle}\n`;
    text += `🔑 *VIN:* ${props.order.vin}\n`;
    text += `👤 *Customer:* ${props.order.customer?.name || 'Valued Customer'}\n\n`;
    text += `📎 *Document:* ${doc.file_name || docLabel}\n`;
    text += `🔗 *Download/View:* ${docUrl}\n\n`;
    text += `Thank you for choosing Bubbles Autos!`;

    // Web Share API support (iOS Safari / Mobile Chrome) - shares actual attached file
    if (doc.file_path && typeof navigator !== 'undefined' && navigator.share && navigator.canShare) {
        try {
            const response = await fetch(`/documents/${doc.id}/download`);
            if (response.ok) {
                const blob = await response.blob();
                const fileName = doc.file_name || `${doc.document_type}.pdf`;
                const file = new File([blob], fileName, { type: doc.mime_type || blob.type || 'application/pdf' });
                
                if (navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: `${docLabel} - Order ${props.order.order_number}`,
                        text: text,
                        files: [file],
                    });
                    showToast.success('Opening Share Sheet...');
                    return;
                }
            }
        } catch (e) {
            console.log('Web Share API fallback to direct link:', e);
        }
    }

    // Direct WhatsApp web/app deep link fallback
    const encodedText = encodeURIComponent(text);
    const url = phone ? `https://wa.me/${phone}?text=${encodedText}` : `https://wa.me/?text=${encodedText}`;
    window.open(url, '_blank');
};

const downloadInvoicePdf = () => {
    window.location.href = `/orders/${props.order.id}/invoice/pdf`;
};

const downloadReceiptPdf = (payment: any) => {
    if (!payment) return;
    window.location.href = `/invoices/payments/${payment.id}/pdf`;
};

// Delete Document Confirm Modal
const deletingDoc = ref<any>(null);
const confirmDeleteDoc = () => {
    if (!deletingDoc.value) return;
    router.delete(`/documents/${deletingDoc.value.id}`, {
        onSuccess: () => {
            deletingDoc.value = null;
            showToast.success('Document permanently deleted.');
        },
    });
};

// Note Form
const noteForm = useForm({
    content: '',
});

const submitNote = () => {
    noteForm.post(`/orders/${props.order.id}/notes`, {
        onSuccess: () => {
            noteForm.reset();
            showToast.success('Staff note posted successfully.');
        },
    });
};

// Document Type Labels
const getDocTypeLabel = (type: string) => {
    switch (type) {
        case 'bill_of_lading':
            return 'Bill of Lading';
        case 'dock_receipt':
            return 'Dock Receipt';
        case 'invoice':
            return 'Invoice Document';
        case 'telex_release':
            return 'Telex Release';
        default:
            return 'Other Document';
    }
};

const formatFileSize = (bytes: number | null | undefined) => {
    if (!bytes) return '';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head :title="`Order ${order.order_number} (${order.vin})`" />

    <div class="space-y-6">
        <!-- Order Header Banner (Mobile First) -->
        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/95 dark:bg-slate-950/90 backdrop-blur-xl text-white shadow-xl dark:shadow-2xl dark:shadow-blue-950/40 border border-slate-800/80 relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                <Car class="w-64 h-64 text-white" />
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="px-3 py-1 rounded-full bg-blue-600 text-white font-extrabold text-xs tracking-wider shadow-md shadow-blue-600/30">
                            {{ order.order_number }}
                        </span>
                        <AppBadge :status="order.status" size="md" />
                        <AppBadge v-if="order.invoice?.status" :status="order.invoice.status" size="md" />
                    </div>

                    <h1 class="text-xl sm:text-3xl font-black text-white tracking-tight">
                        <span v-if="order.make || order.model">{{ order.year ? order.year + ' ' : '' }}{{ order.make }} {{ order.model }}</span>
                        <span v-else class="italic text-slate-400">VIN {{ order.vin }}</span>
                    </h1>

                    <!-- VIN Services & Quick Copy Bar -->
                    <div class="flex items-center gap-2 pt-1 flex-wrap">
                        <div class="flex items-center gap-2 font-mono text-xs sm:text-sm bg-slate-900/90 px-3.5 py-1.5 rounded-xl border border-slate-800 shadow-xs">
                            <ShieldCheck class="w-4 h-4 text-emerald-400 shrink-0" />
                            <span class="text-slate-400">VIN:</span>
                            <span class="font-bold text-blue-400 tracking-wider font-mono">{{ order.vin }}</span>
                            <button
                                @click="copyVin"
                                class="ml-2 text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-slate-800"
                                title="Copy VIN to clipboard"
                            >
                                <Check v-if="copiedVin" class="w-4 h-4 text-emerald-400" />
                                <Copy v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons Stack for Mobile & Desktop -->
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2.5 pt-2 md:pt-0">
                    <AppButton
                        v-if="!order.pictures || order.pictures.length === 0"
                        variant="secondary"
                        size="sm"
                        :loading="syncingApi"
                        @click="syncVehicleApi"
                        title="Query ANK Shipping VIN API for vehicle specs and photos"
                        class="col-span-2 sm:col-span-1 rounded-xl font-bold"
                    >
                        <Sparkles class="w-4 h-4 text-amber-500" />
                        <span>Sync API</span>
                    </AppButton>

                    <AppButton variant="secondary" size="sm" @click="openTrackingModal" class="rounded-xl font-bold">
                        <Calendar class="w-4 h-4 text-blue-400" />
                        <span>Tracking Update</span>
                    </AppButton>

                    <AppButton variant="secondary" size="sm" @click="openEditOrderModal" class="rounded-xl font-bold">
                        <SquarePen class="w-4 h-4" /> Edit
                    </AppButton>

                    <AppButton variant="primary" size="sm" @click="showPaymentModal = true" class="col-span-2 sm:col-span-1 rounded-xl font-bold shadow-md shadow-blue-600/30">
                        <DollarSign class="w-4 h-4" /> Record Payment
                    </AppButton>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (Mobile Touch Scrollable Glass Bar) -->
        <div class="p-1.5 rounded-2xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 shadow-xs flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button
                @click="activeTab = 'overview'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                :class="activeTab === 'overview' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
            >
                <Car class="w-4 h-4" />
                <span>Overview & Photos</span>
            </button>
            <button
                @click="activeTab = 'financials'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                :class="activeTab === 'financials' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
            >
                <DollarSign class="w-4 h-4" />
                <span>Invoice & Payments</span>
            </button>
            <button
                @click="activeTab = 'documents'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                :class="activeTab === 'documents' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
            >
                <FileText class="w-4 h-4" />
                <span>Documents Vault ({{ order.documents?.length || 0 }})</span>
            </button>
            <button
                @click="activeTab = 'emails'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                :class="activeTab === 'emails' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
            >
                <Mail class="w-4 h-4" />
                <span>Linked Emails ({{ order.emails?.length || 0 }})</span>
            </button>
            <button
                @click="activeTab = 'notes'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 flex items-center gap-2 shrink-0 cursor-pointer"
                :class="activeTab === 'notes' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
            >
                <MessageSquare class="w-4 h-4" />
                <span>Staff Notes ({{ order.notes?.length || 0 }})</span>
            </button>
        </div>

        <!-- Tab 1: Overview & Tracking -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Customer Details Card -->
                <AppCard title="Customer Information">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center font-bold text-base">
                                {{ order.customer?.name ? order.customer.name.charAt(0) : 'C' }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">{{ order.customer?.name || 'N/A' }}</h4>
                                <p class="text-xs text-slate-400">Registered Customer</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-slate-600 dark:text-slate-300 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                            <div><strong>Phone:</strong> {{ order.customer?.phone || 'N/A' }}</div>
                            <div v-if="order.customer?.whatsapp"><strong>WhatsApp:</strong> {{ order.customer?.whatsapp }}</div>
                            <div v-if="order.customer?.email"><strong>Email:</strong> {{ order.customer?.email }}</div>
                            <div v-if="order.customer?.address"><strong>Address:</strong> {{ order.customer?.address }}</div>
                        </div>
                    </div>
                </AppCard>

                <!-- Shipping Tracking Details Card -->
                <AppCard title="Shipment Details" class="lg:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Shipping Line</span>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ order.shipping_line || 'Not Specified' }}
                            </p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Destination Port</span>
                            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                                {{ order.destination || 'Not Specified' }}
                            </p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60">
                            <span class="text-xs font-semibold text-slate-400 uppercase">Current Status</span>
                            <div class="mt-1">
                                <AppBadge :status="order.status" size="md" />
                            </div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase">Expected Arrival Date</span>
                                <p class="text-base font-bold text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                                    <Calendar class="w-4 h-4 text-blue-500" />
                                    {{ formatDateOnly(order.expected_arrival) }}
                                </p>
                            </div>
                            <AppButton size="sm" variant="secondary" @click="openTrackingModal">
                                <Calendar class="w-3.5 h-3.5" /> Update Tracking & Status
                            </AppButton>
                        </div>
                    </div>
                </AppCard>
            </div>

            <!-- Vehicle Model Pictures Card -->
            <AppCard title="Vehicle Model Photos Gallery" description="Resolved high-resolution photos via ANK Shipping API">
                <template #headerActions v-if="!order.pictures || order.pictures.length === 0">
                    <AppButton
                        size="sm"
                        variant="secondary"
                        :loading="syncingApi"
                        @click="syncVehicleApi"
                    >
                        <Sparkles class="w-3.5 h-3.5 text-amber-500" /> Fetch VIN Photos
                    </AppButton>
                </template>

                <div v-if="order.pictures && order.pictures.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div
                        v-for="(picUrl, index) in order.pictures"
                        :key="index"
                        class="group relative aspect-4/3 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer shadow-xs"
                        @click="activeImageIndex = index"
                    >
                        <img
                            :src="picUrl"
                            :alt="`${order.make || 'Vehicle'} photo ${index + 1}`"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <ImageIcon class="w-5 h-5 text-white" />
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                    <ImageIcon class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto" />
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">No vehicle model photos fetched yet for this VIN.</p>
                    <AppButton size="sm" variant="primary" class="mt-2" :loading="syncingApi" @click="syncVehicleApi">
                        <Sparkles class="w-4 h-4" /> Fetch Photos & Specs via API
                    </AppButton>
                </div>
            </AppCard>

            <!-- Order Audit Trail Timeline Card (Overview Tab) -->
            <AppCard title="Order Audit Trail Timeline" description="Chronological log of shipment status changes and activities">
                <div class="space-y-4">
                    <div v-for="evt in order.timeline_events" :key="evt.id" class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center font-bold shrink-0 mt-0.5">
                            <Clock class="w-4 h-4" />
                        </div>
                        <div class="flex-1 p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60">
                            <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">{{ evt.event_type }}</div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white mt-0.5">{{ evt.title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ evt.description }}</p>
                            <span class="text-[11px] text-slate-400 block mt-1">{{ new Date(evt.created_at).toLocaleString() }}</span>
                        </div>
                    </div>
                    <div v-if="!order.timeline_events || order.timeline_events.length === 0" class="text-center py-6 text-slate-400 text-xs italic">
                        No timeline events recorded yet.
                    </div>
                </div>
            </AppCard>
        </div>

        <!-- Tab 2: Financials & Invoice -->
        <div v-if="activeTab === 'financials'" class="space-y-6">
            <AppCard title="Invoice Line Items" description="Itemized billing breakdown (Description & Amount)">
                <template #headerActions>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="flex items-center gap-1.5 text-xs font-semibold mr-1">
                            <span class="text-slate-400">Payment Status:</span>
                            <AppBadge :status="order.invoice?.status" size="md" />
                        </div>
                        <AppButton size="sm" variant="primary" @click="downloadInvoicePdf" class="rounded-xl font-bold shadow-xs">
                            <Download class="w-4 h-4" />
                            <span>Download Invoice</span>
                        </AppButton>


                        <AppButton size="sm" variant="primary" @click="openAddItemModal" class="rounded-xl font-bold">
                            <Plus class="w-4 h-4" />
                            <span>Add Item</span>
                        </AppButton>
                    </div>
                </template>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs uppercase font-semibold text-slate-400 border-b border-slate-200/80 dark:border-slate-700/60">
                            <tr>
                                <th class="py-3 px-4">Description</th>
                                <th class="py-3 px-4 text-right">Amount ({{ currencyCode }})</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr v-for="item in order.invoice?.items" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-medium text-slate-900 dark:text-white">{{ item.description }}</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">{{ currencySymbol }}{{ Number(item.amount).toFixed(2) }}</td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            @click="openEditItemModal(item)"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
                                            title="Edit Item"
                                        >
                                            <SquarePen class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="deletingItem = item"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                            title="Delete Item"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Financial Summary Footer -->
                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex flex-col items-end gap-1.5 text-sm">
                    <div class="flex items-center justify-between w-64 text-base font-bold text-slate-900 dark:text-white">
                        <span>Total Invoice Amount:</span>
                        <span>{{ currencySymbol }}{{ Number(order.invoice?.total || 0).toFixed(2) }}</span>
                    </div>
                    <div class="flex items-center justify-between w-64 text-emerald-600 dark:text-emerald-400 font-semibold">
                        <span>Total Paid:</span>
                        <span>{{ currencySymbol }}{{ Number(order.invoice?.paid || 0).toFixed(2) }}</span>
                    </div>
                    <div class="flex items-center justify-between w-64 text-base font-extrabold text-blue-600 dark:text-blue-400 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>Remaining Balance:</span>
                        <span>{{ currencySymbol }}{{ Number(order.invoice?.balance || 0).toFixed(2) }}</span>
                    </div>
                </div>
            </AppCard>

            <!-- Partial Payments Log Table -->
            <AppCard title="Partial Payment Log" description="Record of payments received for this vehicle shipment">
                <template #headerActions>
                    <AppButton size="sm" variant="primary" @click="showPaymentModal = true" class="rounded-xl font-bold">
                        <DollarSign class="w-4 h-4" /> Record Payment
                    </AppButton>
                </template>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs uppercase font-semibold text-slate-400 border-b border-slate-200/80 dark:border-slate-700/60">
                            <tr>
                                <th class="py-3 px-4">Payment Date</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Reference</th>
                                <th class="py-3 px-4">Recorded By</th>
                                <th class="py-3 px-4 text-right">Amount Paid</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr v-for="p in order.invoice?.payments" :key="p.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                    {{ formatDateOnly(p.payment_date) }}
                                </td>
                                <td class="py-3 px-4 capitalize text-slate-700 dark:text-slate-300">
                                    {{ p.method?.replace('_', ' ') }}
                                </td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-500">
                                    {{ p.reference || 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ p.recorded_by?.name || 'Staff' }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                    +{{ currencySymbol }}{{ Number(p.amount).toFixed(2) }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Download Payment Receipt PDF Button -->
                                        <button
                                            @click="downloadReceiptPdf(p)"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-955 transition-colors"
                                            title="Download Official Payment Receipt PDF"
                                        >
                                            <Download class="w-4 h-4" />
                                        </button>

                                        <button
                                            @click="deletingPayment = p"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                            title="Delete Payment Entry"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>
        </div>

        <!-- Tab 3: Documents Vault -->
        <div v-if="activeTab === 'documents'" class="space-y-6">
            <AppCard
                title="Vehicle Shipment Documents Vault"
                description="Vital downloadable shipment records (Bill of Lading, Dock Receipt, Invoice Document) and printable Telex Release messages"
            >
                <template #headerActions>
                    <AppButton size="sm" variant="primary" @click="showDocumentModal = true" class="rounded-xl font-bold">
                        <Plus class="w-4 h-4" /> Add / Upload Document
                    </AppButton>
                </template>

                <div v-if="!order.documents || order.documents.length === 0" class="text-center py-12 text-slate-400">
                    <FileText class="w-12 h-12 mx-auto mb-2 text-slate-300" />
                    <p class="text-sm">No documents uploaded for this order yet.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="doc in order.documents"
                        :key="doc.id"
                        class="p-4 rounded-xl border bg-white dark:bg-slate-900 flex flex-col justify-between space-y-3 shadow-xs"
                        :class="[
                            ['bill_of_lading', 'dock_receipt', 'invoice'].includes(doc.document_type)
                                ? 'border-blue-300 dark:border-blue-800/60 ring-1 ring-blue-500/10'
                                : doc.document_type === 'telex_release'
                                ? 'border-amber-300 dark:border-amber-800/60 ring-1 ring-amber-500/10'
                                : 'border-slate-200 dark:border-slate-700'
                        ]"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center font-bold"
                                    :class="doc.document_type === 'telex_release' ? 'bg-amber-100 text-amber-600 dark:bg-amber-950/50' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-600'"
                                >
                                    <Printer v-if="doc.document_type === 'telex_release'" class="w-5 h-5" />
                                    <FileCheck v-else-if="['bill_of_lading', 'dock_receipt', 'invoice'].includes(doc.document_type)" class="w-5 h-5 text-blue-600" />
                                    <FileText v-else class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white truncate max-w-[180px]">{{ getDocTypeLabel(doc.document_type) }}</h4>
                                    <span
                                        class="text-[10px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded-xs animate-pulse bg-blue-100 text-blue-850 dark:bg-blue-900/50 dark:text-blue-300"
                                    >
                                        {{ getDocTypeLabel(doc.document_type) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Attached File Details Badge -->
                        <div v-if="doc.file_name" class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-lg border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                            <span class="font-mono text-slate-800 dark:text-slate-200 truncate max-w-[160px]" :title="doc.file_name">📎 {{ doc.file_name }}</span>
                            <span v-if="doc.file_size" class="text-[10px] text-slate-400 font-sans ml-1 shrink-0">{{ formatFileSize(doc.file_size) }}</span>
                        </div>

                        <!-- Content snippet preview for Telex Release -->
                        <div v-if="doc.document_type === 'telex_release' && doc.content" class="text-xs text-slate-600 dark:text-slate-300 bg-amber-50/60 dark:bg-amber-950/20 p-2.5 rounded-lg font-mono line-clamp-2 border border-amber-200/50 dark:border-amber-900/30">
                            {{ doc.content }}
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <!-- Download Link for files -->
                                <a
                                    v-if="doc.file_path"
                                    :href="`/documents/${doc.id}/download`"
                                    target="_blank"
                                    class="text-blue-600 font-bold hover:underline flex items-center gap-1"
                                >
                                    <Download class="w-3.5 h-3.5" /> Download
                                </a>

                                <!-- WhatsApp Telex Release (Text) -->
                                <button
                                    v-if="doc.document_type === 'telex_release' || doc.content"
                                    @click="shareTelexViaWhatsapp(doc)"
                                    class="text-emerald-600 dark:text-emerald-400 font-bold hover:bg-emerald-100 dark:hover:bg-emerald-900/60 flex items-center gap-1.5 cursor-pointer bg-emerald-50 dark:bg-emerald-950/50 px-2 py-1 rounded-md border border-emerald-200 dark:border-emerald-800/60 transition-colors"
                                    title="Send Telex Release text directly via WhatsApp"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    WhatsApp Telex
                                </button>

                                <!-- WhatsApp File Document (BL, Dock Receipt, Invoice) -->
                                <button
                                    v-if="doc.file_path"
                                    @click="shareDocumentViaWhatsapp(doc)"
                                    class="text-emerald-600 dark:text-emerald-400 font-bold hover:bg-emerald-100 dark:hover:bg-emerald-900/60 flex items-center gap-1.5 cursor-pointer bg-emerald-50 dark:bg-emerald-950/50 px-2 py-1 rounded-md border border-emerald-200 dark:border-emerald-800/60 transition-colors"
                                    title="Send file (BL, Dock Receipt) via WhatsApp"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    Send File
                                </button>

                                <!-- Print Telex Release Text Viewer Button -->
                                <button
                                    v-if="doc.document_type === 'telex_release' || doc.content"
                                    @click="printingTelexDoc = doc"
                                    class="text-amber-600 dark:text-amber-400 font-bold hover:underline flex items-center gap-1 cursor-pointer"
                                >
                                    <Printer class="w-3.5 h-3.5" /> View / Print Text
                                </button>
                            </div>

                            <button
                                @click="deletingDoc = doc"
                                class="text-red-500 hover:text-red-700 p-1"
                                title="Delete Document"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </AppCard>
        </div>

        <!-- Tab 4: Linked Emails -->
        <div v-if="activeTab === 'emails'" class="space-y-6">
            <AppCard title="Linked Communications" description="Emails automatically matched or linked to this order">
                <div v-if="!order.emails || order.emails.length === 0" class="text-center py-10 text-slate-400">
                    No email communications linked to this order yet.
                </div>
                <div v-else class="space-y-4">
                    <div v-for="em in order.emails" :key="em.id" class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-3 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                            <span class="truncate">From: <strong class="text-slate-800 dark:text-slate-100 font-mono">{{ em.sender }}</strong></span>
                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                <span class="text-[11px]">{{ new Date(em.received_at).toLocaleString() }}</span>
                                <button
                                    @click="promptUnlinkEmailFromOrder(em)"
                                    class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 font-extrabold px-2.5 py-1 rounded-lg transition-colors cursor-pointer flex items-center gap-1 shrink-0 bg-red-50/50 dark:bg-red-950/20 border border-red-200/60 dark:border-red-900/40"
                                    title="Unlink this email from order"
                                >
                                    <Unlink class="w-3.5 h-3.5" />
                                    <span>Unlink</span>
                                </button>
                            </div>
                        </div>
                        <h4 class="font-extrabold text-base text-slate-900 dark:text-white">{{ em.subject }}</h4>
                        <div
                            class="text-xs text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/80 p-4 rounded-xl border border-slate-200/80 dark:border-slate-700/60 leading-relaxed max-h-72 overflow-y-auto prose dark:prose-invert max-w-none"
                            v-html="em.body"
                        ></div>

                        <!-- Attachments list for linked email -->
                        <div v-if="em.attachments && em.attachments.length > 0" class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block">Email Attachments ({{ em.attachments.length }})</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div
                                    v-for="att in em.attachments"
                                    :key="att.id"
                                    class="p-2.5 rounded-lg bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200/60 dark:border-blue-800/50 flex items-center justify-between gap-2 text-xs"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <Paperclip class="w-3.5 h-3.5 text-blue-600 shrink-0" />
                                        <span class="font-bold text-slate-900 dark:text-white truncate" :title="att.filename">{{ att.filename }}</span>
                                    </div>
                                    <a
                                        :href="`/email-attachments/${att.id}/download`"
                                        target="_blank"
                                        class="text-blue-600 font-bold hover:underline flex items-center gap-1 shrink-0 text-[11px]"
                                    >
                                        <Download class="w-3 h-3" /> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </AppCard>
        </div>

        <!-- Tab 5: Staff Notes -->
        <div v-if="activeTab === 'notes'" class="space-y-6">
            <AppCard title="Staff Internal Notes Stream">
                <form @submit.prevent="submitNote" class="space-y-3 mb-6">
                    <AppFormField label="Add Internal Staff Note" required :error="noteForm.errors.content">
                        <AppTextarea v-model="noteForm.content" placeholder="Write internal order update or customer note..." :rows="3" />
                    </AppFormField>
                    <div class="flex justify-end">
                        <AppButton type="submit" variant="primary" :loading="noteForm.processing">Post Note</AppButton>
                    </div>
                </form>

                <div class="space-y-4">
                    <div v-for="n in order.notes" :key="n.id" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shrink-0">
                            {{ n.user?.name ? n.user.name.charAt(0) : 'U' }}
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-900 dark:text-white">{{ n.user?.name || 'Staff' }}</span>
                                <span class="text-slate-400">{{ new Date(n.created_at).toLocaleString() }}</span>
                            </div>
                            <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ n.content }}</p>
                        </div>
                    </div>
                </div>
            </AppCard>
        </div>
    </div>

    <!-- Update Tracking & Expected Delivery Modal -->
    <AppModal :show="showTrackingModal" title="Update Tracking & Expected Delivery" @close="showTrackingModal = false">
        <form @submit.prevent="submitTrackingUpdate" class="space-y-4">
            <AppFormField label="Expected Delivery Date" :error="trackingForm.errors.expected_arrival">
                <AppDatePicker v-model="trackingForm.expected_arrival" />
            </AppFormField>
            
            <AppFormField label="Update Shipment Status (Optional)" :error="trackingForm.errors.status">
                <AppSelect
                    v-model="trackingForm.status"
                    :options="statusOptions"
                    placeholder="Leave unchanged or pick new status..."
                />
            </AppFormField>

            <AppFormField label="Tracking Log / Operational Notes (Optional)" :error="trackingForm.errors.notes">
                <AppTextarea v-model="trackingForm.notes" placeholder="Add tracking details or notes about status change..." :rows="3" />
            </AppFormField>

            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showTrackingModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="trackingForm.processing">
                    Save Tracking & Status
                </AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Add / Edit Invoice Line Item Modal -->
    <AppModal :show="showItemModal" :title="editingItem ? 'Edit Invoice Line Item' : 'Add Invoice Line Item'" @close="showItemModal = false">
        <form @submit.prevent="submitItemForm" class="space-y-4">
            <!-- Preset Item Selector Dropdown -->
            <AppFormField v-if="!editingItem && props.invoiceItemTemplates && props.invoiceItemTemplates.length > 0" label="Select Preset Invoice Item (Optional)">
                <AppSelect
                    :modelValue="selectedTemplateId"
                    @update:modelValue="onPresetTemplateSelected"
                    :options="props.invoiceItemTemplates.map(t => ({ value: t.id, label: `${t.description} ($${Number(t.default_amount).toFixed(2)})` }))"
                    placeholder="Choose from standardized invoice items catalog..."
                />
            </AppFormField>

            <AppFormField label="Item Description" required :error="itemForm.errors.description">
                <AppInput v-model="itemForm.description" placeholder="e.g. Vehicle Shipping Fee - Houston to Lagos" />
            </AppFormField>
            <AppFormField label="Amount (USD)" required :error="itemForm.errors.amount">
                <AppInput type="number" v-model="itemForm.amount" placeholder="0.00" step="0.01" min="0" />
            </AppFormField>
            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showItemModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="itemForm.processing">
                    <Plus class="w-4 h-4" /> {{ editingItem ? 'Update Item' : 'Add Item' }}
                </AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Record Payment Modal -->
    <AppModal :show="showPaymentModal" title="Record Partial Payment" @close="showPaymentModal = false">
        <form @submit.prevent="submitPayment" class="space-y-4">
            <AppFormField label="Payment Amount (USD)" required :error="paymentForm.errors.amount">
                <AppInput type="number" v-model="paymentForm.amount" step="0.01" min="0.01" placeholder="0.00" />
            </AppFormField>
            <AppFormField label="Payment Date" required :error="paymentForm.errors.payment_date">
                <AppDatePicker v-model="paymentForm.payment_date" />
            </AppFormField>
            <AppFormField label="Payment Method" required :error="paymentForm.errors.method">
                <AppSelect
                    v-model="paymentForm.method"
                    :options="[
                        { value: 'bank_transfer', label: 'Bank Transfer' },
                        { value: 'cash', label: 'Cash' },
                        { value: 'wire', label: 'Wire Transfer' },
                        { value: 'card', label: 'Card' },
                        { value: 'zelle', label: 'Zelle' },
                    ]"
                />
            </AppFormField>
            <AppFormField label="Transaction Reference" :error="paymentForm.errors.reference">
                <AppInput v-model="paymentForm.reference" placeholder="e.g. WIRE-99812" />
            </AppFormField>
            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showPaymentModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="paymentForm.processing">
                    <DollarSign class="w-4 h-4" /> Record Payment
                </AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Edit Order Information Modal -->
    <AppModal :show="showEditOrderModal" title="Edit Order Information & VIN Lookup" maxWidth="lg" @close="showEditOrderModal = false">
        <form @submit.prevent="submitEditOrder" class="space-y-4">
            <AppFormField label="Customer" required :error="editOrderForm.errors.customer_id">
                <AppSelect v-model="editOrderForm.customer_id" :options="customerOptions" placeholder="Select customer..." />
            </AppFormField>

            <AppFormField label="17-Digit VIN" required :error="editOrderForm.errors.vin">
                <div class="flex items-center gap-2">
                    <AppInput v-model="editOrderForm.vin" placeholder="1FA6P8CF0H5123456" class="font-mono uppercase flex-1" maxlength="17" />
                    <AppButton
                        type="button"
                        variant="secondary"
                        size="sm"
                        :loading="lookingUpVin"
                        :disabled="editOrderForm.vin.length !== 17"
                        @click="lookupVinInEditModal"
                        title="Query ANK Shipping VIN API to auto-fill vehicle make, model, and year"
                    >
                        <Sparkles class="w-4 h-4 text-amber-500" /> Lookup VIN API
                    </AppButton>
                </div>
            </AppFormField>

            <div class="grid grid-cols-3 gap-3">
                <AppFormField label="Make" :error="editOrderForm.errors.make">
                    <AppInput v-model="editOrderForm.make" placeholder="Ford" />
                </AppFormField>
                <AppFormField label="Model" :error="editOrderForm.errors.model">
                    <AppInput v-model="editOrderForm.model" placeholder="Mustang GT" />
                </AppFormField>
                <AppFormField label="Year" :error="editOrderForm.errors.year">
                    <AppInput type="number" v-model="editOrderForm.year" placeholder="2022" />
                </AppFormField>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <AppFormField label="Color" :error="editOrderForm.errors.color">
                    <AppInput v-model="editOrderForm.color" placeholder="Black" />
                </AppFormField>
                <AppFormField label="Shipping Line" :error="editOrderForm.errors.shipping_line">
                    <AppSelect
                        v-model="editOrderForm.shipping_line"
                        :options="[
                            { value: 'Sallaum Lines', label: 'Sallaum Lines' },
                            { value: 'Grimaldi Lines', label: 'Grimaldi Lines' },
                            { value: 'MSC Line', label: 'MSC Line' },
                        ]"
                        placeholder="Select Shipping Line..."
                    />
                </AppFormField>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <AppFormField label="Destination Port" :error="editOrderForm.errors.destination">
                    <AppSelect
                        v-model="editOrderForm.destination"
                        :options="[
                            { value: 'Lagos, Nigeria', label: 'Lagos, Nigeria' },
                            { value: 'Cotonou, Benin', label: 'Cotonou, Benin' },
                        ]"
                        placeholder="Select Destination Port..."
                    />
                </AppFormField>
                <AppFormField label="Expected Arrival Date" :error="editOrderForm.errors.expected_arrival">
                    <AppDatePicker v-model="editOrderForm.expected_arrival" />
                </AppFormField>
            </div>

            <div class="flex justify-end gap-3 pt-3">
                <AppButton variant="outline" @click="showEditOrderModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="editOrderForm.processing">
                    Save Order Changes
                </AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Upload Document Modal -->
    <AppModal :show="showDocumentModal" title="Add / Upload Order Document" @close="showDocumentModal = false">
        <form @submit.prevent="submitDocumentUpload" class="space-y-4">
            <AppFormField label="Document Type" required :error="documentForm.errors.document_type">
                <AppSelect
                    v-model="documentForm.document_type"
                    :options="[
                        { value: 'bill_of_lading', label: 'Bill of Lading (Downloadable File)' },
                        { value: 'dock_receipt', label: 'Dock Receipt (Downloadable File)' },
                        { value: 'invoice', label: 'Invoice Document (Downloadable File)' },
                        { value: 'telex_release', label: 'Telex Release (Printable Text)' },
                        { value: 'other', label: 'Other Document' },
                    ]"
                />
            </AppFormField>

            <!-- Telex Release Text Content -->
            <AppFormField
                v-if="documentForm.document_type === 'telex_release'"
                label="Telex Release Text / Extracted Email Body"
                required
                :error="documentForm.errors.content"
            >
                <AppTextarea
                    v-model="documentForm.content"
                    placeholder="Paste Telex Release message extracted from email or shipping notice..."
                    :rows="6"
                    class="font-mono text-xs"
                />
            </AppFormField>

            <!-- File Upload Input -->
            <AppFormField
                label="Attach Document File"
                :required="documentForm.document_type !== 'telex_release'"
                :error="documentForm.errors.file"
            >
                <FileUploader
                    v-model="documentForm.file"
                    @file-selected="handleFileSelected"
                    @file-removed="handleFileSelected(null)"
                    @change="handleFileSelected"
                />
            </AppFormField>

            <AppFormField label="Update Shipment Status (Optional)" :error="documentForm.errors.status">
                <AppSelect
                    v-model="documentForm.status"
                    :options="statusOptions"
                    placeholder="Leave unchanged or pick new status..."
                />
            </AppFormField>

            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showDocumentModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="documentForm.processing">Attach & Save Document</AppButton>
            </div>
        </form>
    </AppModal>



    <!-- Confirm Unlink Email Modal -->
    <AppModal :show="!!unlinkingEmailConfirm" title="Confirm Unlink Email" @close="unlinkingEmailConfirm = null">
        <div v-if="unlinkingEmailConfirm" class="space-y-4">
            <p class="text-xs text-slate-600 dark:text-slate-300">
                Are you sure you want to unlink email <strong class="text-slate-900 dark:text-white">"{{ unlinkingEmailConfirm.subject }}"</strong> from Order <strong class="text-slate-900 dark:text-white">{{ order.order_number }}</strong>?
            </p>
            <div class="p-3 bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-900/40 rounded-xl text-xs text-amber-800 dark:text-amber-300 space-y-1">
                <strong class="font-bold">Note:</strong>
                <p>Unlinking this email will remove it from this vehicle order and return it to the <strong>Needs Review</strong> inbox state.</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="unlinkingEmailConfirm = null">Cancel</AppButton>
                <AppButton variant="danger" :loading="unlinkingEmailProcessing" @click="confirmUnlinkEmailFromOrder">
                    Yes, Unlink Email
                </AppButton>
            </div>
        </div>
    </AppModal>

    <!-- Telex Release Text Viewer & Print Modal -->
    <AppModal :show="!!printingTelexDoc" title="Telex Release Details" maxWidth="2xl" @close="printingTelexDoc = null">
        <div v-if="printingTelexDoc" class="space-y-6">
            <div id="telex-print-area" class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="font-extrabold text-base uppercase text-amber-600 dark:text-amber-400">Telex Release Notice</h3>
                        <p class="text-xs text-slate-500 font-mono">Order#: {{ order.order_number }} | VIN: {{ order.vin }}</p>
                    </div>
                    <div class="text-right text-xs text-slate-500">
                        <div>Customer: <strong class="text-slate-900 dark:text-white">{{ order.customer?.name }}</strong></div>
                        <div>Date: {{ formatDateOnly(printingTelexDoc.created_at || new Date().toISOString()) }}</div>
                    </div>
                </div>

                <div class="bg-amber-50/60 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-200/50 dark:border-amber-900/30 text-xs font-mono leading-relaxed whitespace-pre-wrap">
                    {{ printingTelexDoc.content || 'No text content attached.' }}
                </div>
            </div>

            <div class="flex justify-end gap-3 print:hidden">
                <AppButton variant="outline" @click="printingTelexDoc = null">Close</AppButton>
                <AppButton variant="amber" @click="shareTelexViaWhatsapp(printingTelexDoc)">
                    <svg class="w-4 h-4 fill-current text-white inline-block mr-1" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Send to WhatsApp
                </AppButton>
                <AppButton variant="primary" @click="printTelexRelease">
                    <Printer class="w-4 h-4" /> Print Text
                </AppButton>
            </div>
        </div>
    </AppModal>

    <!-- Confirm Remove Item Modal -->
    <ConfirmModal
        :show="!!deletingItem"
        title="Delete Invoice Line Item?"
        message="Are you sure you want to remove this line item? The invoice total will recalculate automatically."
        @close="deletingItem = null"
        @confirm="confirmRemoveItem"
    />

    <!-- Confirm Payment Delete Modal -->
    <ConfirmModal
        :show="!!deletingPayment"
        title="Delete Payment Entry?"
        message="Are you sure you want to delete this payment record? The invoice total paid and remaining balance will recalculate."
        @close="deletingPayment = null"
        @confirm="confirmDeletePayment"
    />

    <!-- Confirm Document Delete Modal -->
    <ConfirmModal
        :show="!!deletingDoc"
        title="Permanently Delete Document?"
        message="This action will permanently delete the file/record. This action cannot be undone."
        @close="deletingDoc = null"
        @confirm="confirmDeleteDoc"
    />
</template>
