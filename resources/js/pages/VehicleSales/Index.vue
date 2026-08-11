<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import {
    Plus,
    Search,
    Download,
    FileText,
    Receipt,
    Eye,
    Pencil,
    Calendar,
    FilterX,
    Car,
} from '@lucide/vue';

const props = defineProps<{
    sales: {
        data: Array<{
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
            sale_date: string;
            sale_amount: number;
            amount_paid: number;
            payment_method: string | null;
            creator?: { name: string } | null;
        }>;
        links: Array<any>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');

const applyFilters = () => {
    router.get(
        '/vehicle-sales',
        {
            search: search.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    applyFilters();
};

const downloadInvoicePdf = (saleId: number) => {
    window.location.href = `/vehicle-sales/${saleId}/invoice/pdf`;
};

const downloadReceiptPdf = (saleId: number) => {
    window.location.href = `/vehicle-sales/${saleId}/receipt/pdf`;
};
</script>

<template>
    <Head title="Vehicle Sales Documentation" />

    <div class="space-y-6">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <Car class="w-7 h-7 text-blue-600 dark:text-blue-400" />
                        <span>Vehicle Sales Documentation</span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Record completed sales transactions and automatically generate official Invoices and Cash Receipts.
                    </p>
                </div>
                <Link href="/vehicle-sales/create">
                    <AppButton variant="primary" size="md" class="rounded-xl font-bold shadow-sm">
                        <Plus class="w-4 h-4 mr-1.5" />
                        <span>+ New Vehicle Sale</span>
                    </AppButton>
                </Link>
            </div>

            <!-- Filters Bar -->
            <AppCard>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <Search class="w-4 h-4 absolute left-3.5 top-3 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by customer name, phone, vehicle, VIN, or sale number..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100 placeholder-slate-400"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <AppButton variant="primary" size="md" @click="applyFilters" class="rounded-xl font-bold">
                            Search
                        </AppButton>
                        <button
                            v-if="search"
                            @click="resetFilters"
                            class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors flex items-center gap-1"
                        >
                            <FilterX class="w-3.5 h-3.5" /> Clear
                        </button>
                    </div>
                </div>

                <div class="mt-3 text-xs text-slate-500">
                    Showing <strong>{{ sales.total }}</strong> vehicle sales records
                </div>
            </AppCard>

            <!-- Sales Table -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs uppercase font-semibold text-slate-400 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            <tr>
                                <th class="py-3.5 px-4">Sale No.</th>
                                <th class="py-3.5 px-4">Customer</th>
                                <th class="py-3.5 px-4">Vehicle Details</th>
                                <th class="py-3.5 px-4 text-right">Sale Amount</th>
                                <th class="py-3.5 px-4 text-right">Amount Paid</th>
                                <th class="py-3.5 px-4">Date</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="s in sales.data" :key="s.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-850/60 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                                    <Link :href="`/vehicle-sales/${s.id}`" class="hover:underline">
                                        {{ s.sale_number }}
                                    </Link>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ s.customer_name }}</div>
                                    <div v-if="s.customer_phone" class="text-xs text-slate-500">{{ s.customer_phone }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-900 dark:text-white">
                                        {{ [s.vehicle_year, s.vehicle_make, s.vehicle_model].filter(Boolean).join(' ') }}
                                    </div>
                                    <div v-if="s.vehicle_vin" class="text-xs font-mono text-slate-400">VIN: {{ s.vehicle_vin }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-white font-mono">
                                    ₦{{ Number(s.sale_amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                    ₦{{ Number(s.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                    {{ new Date(s.sale_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/vehicle-sales/${s.id}`" title="View Sale">
                                            <button class="p-1.5 text-slate-500 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                                <Eye class="w-4 h-4" />
                                            </button>
                                        </Link>

                                        <Link :href="`/vehicle-sales/${s.id}/edit`" title="Edit Sale">
                                            <button class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">
                                                <Pencil class="w-4 h-4" />
                                            </button>
                                        </Link>

                                        <button
                                            @click="downloadInvoicePdf(s.id)"
                                            class="p-1.5 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors"
                                            title="Download Invoice PDF"
                                        >
                                            <FileText class="w-4 h-4" />
                                        </button>

                                        <button
                                            @click="downloadReceiptPdf(s.id)"
                                            class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors"
                                            title="Download Cash Receipt PDF"
                                        >
                                            <Receipt class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="sales.data.length === 0">
                                <td colspan="7" class="text-center py-10 text-slate-400 text-sm italic">
                                    No vehicle sales records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="sales.last_page > 1" class="mt-6 flex justify-end">
                    <AppPagination :links="sales.links" />
                </div>
            </AppCard>
        </div>
</template>
