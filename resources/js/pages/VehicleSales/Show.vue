<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import { convertNumberToWords } from '@/lib/numberToWords';
import {
    ArrowLeft,
    FileText,
    Receipt,
    Pencil,
    Plus,
    X,
    Save,
    CheckCircle2,
    Clock,
    AlertCircle,
} from '@lucide/vue';

interface Payment {
    id: number;
    receipt_number: string;
    amount_paid: number;
    payment_date: string;
    payment_method: string;
    amount_in_words: string | null;
    notes: string | null;
    created_at: string;
    creator?: { name: string } | null;
}

const props = defineProps<{
    sale: {
        id: number;
        sale_number: string;
        customer_name: string;
        customer_phone: string | null;
        customer_address: string | null;
        vehicle_make: string;
        vehicle_model: string;
        vehicle_year: string | null;
        vehicle_vin: string | null;
        vehicle_color: string | null;
        vehicle_description: string | null;
        sale_date: string;
        sale_amount: number;
        amount_paid: number;
        total_paid: number;
        balance_due: number;
        payment_status: 'paid' | 'partially_paid' | 'unpaid';
        payment_method: string | null;
        amount_in_words: string | null;
        notes: string | null;
        created_at: string;
        creator?: { name: string } | null;
        payments?: Payment[];
    };
    companySettings: {
        name: string;
        address: string;
        phone: string;
        currency_symbol: string;
        currency_code: string;
    };
}>();

const isPaymentModalOpen = ref(false);

const paymentForm = useForm({
    amount_paid: '',
    payment_date: new Date().toISOString().substring(0, 10),
    payment_method: 'bank_transfer',
    amount_in_words: '',
    notes: '',
});

const openPaymentModal = () => {
    paymentForm.clearErrors();
    const remaining = props.sale.balance_due > 0 ? props.sale.balance_due.toString() : '';
    paymentForm.amount_paid = remaining;
    paymentForm.amount_in_words = remaining ? convertNumberToWords(remaining) : '';
    isPaymentModalOpen.value = true;
};

// Auto-populate amount in words for payment installment
watch(
    () => paymentForm.amount_paid,
    (val) => {
        if (val) {
            paymentForm.amount_in_words = convertNumberToWords(val);
        }
    }
);

const submitPayment = () => {
    paymentForm.clearErrors();
    const amount = Number(paymentForm.amount_paid);
    const maxAllowed = Number(props.sale.balance_due);

    if (amount > maxAllowed + 0.01) {
        paymentForm.setError(
            'amount_paid',
            `Payment amount (${props.companySettings.currency_symbol}${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}) cannot exceed remaining balance (${props.companySettings.currency_symbol}${maxAllowed.toLocaleString('en-US', { minimumFractionDigits: 2 })}).`
        );
        return;
    }

    paymentForm.post(`/vehicle-sales/${props.sale.id}/payments`, {
        preserveScroll: true,
        onSuccess: () => {
            isPaymentModalOpen.value = false;
            paymentForm.reset('amount_paid', 'amount_in_words', 'notes');
        },
    });
};

const downloadInvoicePdf = () => {
    window.location.href = `/vehicle-sales/${props.sale.id}/invoice/pdf`;
};

const downloadReceiptPdf = (paymentId?: number) => {
    if (paymentId) {
        window.location.href = `/vehicle-sales/${props.sale.id}/payments/${paymentId}/receipt/pdf`;
    } else {
        window.location.href = `/vehicle-sales/${props.sale.id}/receipt/pdf`;
    }
};
</script>

<template>
    <Head :title="`Sale ${sale.sale_number} - ${sale.customer_name}`" />

    <div class="space-y-6">
        <!-- Top Header & Action Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <Link href="/vehicle-sales">
                    <button class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </button>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Sale {{ sale.sale_number }}</h1>
                        <!-- Payment Status Badges -->
                        <span
                            v-if="sale.payment_status === 'paid'"
                            class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 flex items-center gap-1"
                        >
                            <CheckCircle2 class="w-3.5 h-3.5" /> Fully Paid
                        </span>
                        <span
                            v-else-if="sale.payment_status === 'partially_paid'"
                            class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 flex items-center gap-1"
                        >
                            <Clock class="w-3.5 h-3.5" /> Partially Paid
                        </span>
                        <span
                            v-else
                            class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 flex items-center gap-1"
                        >
                            <AlertCircle class="w-3.5 h-3.5" /> Unpaid
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Customer: <strong>{{ sale.customer_name }}</strong> | Date: {{ new Date(sale.sale_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <AppButton variant="outline" size="md" @click="openPaymentModal" class="rounded-xl font-bold border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40">
                    <Plus class="w-4 h-4 mr-1.5" /> Record Payment
                </AppButton>

                <Link :href="`/vehicle-sales/${sale.id}/edit`">
                    <AppButton variant="outline" size="md" class="rounded-xl font-bold">
                        <Pencil class="w-4 h-4 mr-1.5" /> Edit Sale
                    </AppButton>
                </Link>

                <AppButton variant="primary" size="md" @click="downloadInvoicePdf" class="rounded-xl font-bold shadow-sm">
                    <FileText class="w-4 h-4 mr-1.5" /> Download Invoice PDF
                </AppButton>
            </div>
        </div>

        <!-- Transaction Summary Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Customer Details -->
            <AppCard title="Customer Information">
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Name</span>
                        <span class="font-bold text-slate-900 dark:text-white text-base">{{ sale.customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Phone</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ sale.customer_phone || 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Address</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ sale.customer_address || 'Kano, Nigeria' }}</span>
                    </div>
                </div>
            </AppCard>

            <!-- Vehicle Details -->
            <AppCard title="Vehicle Information">
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Vehicle Description</span>
                        <span class="font-bold text-slate-900 dark:text-white text-base">
                            {{ [sale.vehicle_year, sale.vehicle_make, sale.vehicle_model].filter(Boolean).join(' ') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">VIN / Chassis Number</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ sale.vehicle_vin || 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Color</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ sale.vehicle_color || 'N/A' }}</span>
                    </div>
                </div>
            </AppCard>

            <!-- Financial Breakdown -->
            <AppCard title="Financial Overview">
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Total Invoice Sale Amount</span>
                        <span class="font-mono font-black text-slate-900 dark:text-white text-xl">
                            {{ companySettings.currency_symbol }}{{ Number(sale.sale_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Total Amount Paid</span>
                        <span class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-lg">
                            {{ companySettings.currency_symbol }}{{ Number(sale.total_paid || sale.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 block">Remaining Balance Due</span>
                        <span class="font-mono font-black text-base" :class="sale.balance_due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500'">
                            {{ companySettings.currency_symbol }}{{ Number(sale.balance_due).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>
                </div>
            </AppCard>
        </div>

        <!-- Payment Receipts History Table (Supports Multiple Receipts per Sale) -->
        <AppCard title="Cash Receipts & Installment Payment History" description="Each payment installment generates a unique cash receipt overlay document">
            <template #actions>
                <AppButton variant="primary" size="sm" @click="openPaymentModal" class="rounded-xl font-bold">
                    <Plus class="w-3.5 h-3.5 mr-1" /> Record New Payment
                </AppButton>
            </template>

            <div v-if="sale.payments && sale.payments.length > 0" class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-bold uppercase text-slate-500">
                            <th class="py-3 px-4">Receipt #</th>
                            <th class="py-3 px-4">Payment Date</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4">Amount Paid</th>
                            <th class="py-3 px-4">Recorded By</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="payment in sale.payments" :key="payment.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                {{ payment.receipt_number }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                {{ new Date(payment.payment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                            </td>
                            <td class="py-3.5 px-4 capitalize font-medium text-slate-700 dark:text-slate-300">
                                {{ (payment.payment_method || 'bank_transfer').replace('_', ' ') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                {{ companySettings.currency_symbol }}{{ Number(payment.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-500">
                                {{ payment.creator?.name || 'Staff' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <AppButton variant="amber" size="sm" @click="downloadReceiptPdf(payment.id)" class="rounded-xl font-bold shadow-xs">
                                    <Receipt class="w-3.5 h-3.5 mr-1" /> Receipt PDF
                                </AppButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="text-center py-8 text-slate-500">
                <Receipt class="w-10 h-10 mx-auto text-slate-400 mb-2 opacity-50" />
                <p class="text-sm font-medium">No payment receipts issued yet.</p>
                <button @click="isPaymentModalOpen = true" class="mt-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    + Record First Payment
                </button>
            </div>
        </AppCard>

        <!-- Amount in Words & Notes -->
        <AppCard>
            <div class="space-y-4 text-sm">
                <div>
                    <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Amount in Words (Primary)</span>
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-800 font-serif italic font-medium text-slate-900 dark:text-slate-100">
                        "{{ sale.amount_in_words }}"
                    </div>
                </div>

                <div v-if="sale.notes">
                    <span class="text-xs font-bold uppercase text-slate-400 block mb-1">Notes / Remarks</span>
                    <p class="text-slate-600 dark:text-slate-400 text-xs">{{ sale.notes }}</p>
                </div>
            </div>
        </AppCard>
    </div>

    <!-- Record Installment Payment Modal -->
    <div v-if="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Record Payment Installment</h3>
                    <p class="text-xs text-slate-500">Generate a new official Cash Receipt for Sale #{{ sale.sale_number }}</p>
                </div>
                <button @click="isPaymentModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitPayment" class="space-y-4">
                <AppFormField label="Amount Paid (₦)" required :error="paymentForm.errors.amount_paid">
                    <AppInput
                        v-model="paymentForm.amount_paid"
                        type="number"
                        step="0.01"
                        placeholder="e.g. 35000000"
                        :error="!!paymentForm.errors.amount_paid"
                    />
                </AppFormField>

                <div class="grid grid-cols-2 gap-4">
                    <AppFormField label="Payment Date" required :error="paymentForm.errors.payment_date">
                        <AppInput
                            v-model="paymentForm.payment_date"
                            type="date"
                            :error="!!paymentForm.errors.payment_date"
                        />
                    </AppFormField>

                    <AppFormField label="Payment Method" :error="paymentForm.errors.payment_method">
                        <select
                            v-model="paymentForm.payment_method"
                            class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="wire">Wire Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </AppFormField>
                </div>

                <AppFormField label="Amount in Words (Auto-generated)" :error="paymentForm.errors.amount_in_words">
                    <AppInput
                        v-model="paymentForm.amount_in_words"
                        placeholder="e.g. Thirty-Five Million Naira Only"
                        :error="!!paymentForm.errors.amount_in_words"
                    />
                </AppFormField>

                <AppFormField label="Payment Remarks / Reference (Optional)" :error="paymentForm.errors.notes">
                    <textarea
                        v-model="paymentForm.notes"
                        rows="2"
                        placeholder="e.g. Part payment second installment..."
                        class="w-full px-3.5 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                </AppFormField>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <AppButton variant="outline" type="button" @click="isPaymentModalOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="paymentForm.processing" class="rounded-xl font-bold">
                        <Save class="w-4 h-4 mr-1.5" /> Save Payment & Issue Receipt
                    </AppButton>
                </div>
            </form>
        </div>
    </div>
</template>
