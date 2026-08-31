<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import GlobalVinSearch from '@/components/common/GlobalVinSearch.vue';
import { Car, CheckCircle2, DollarSign, Mail, Clock, ArrowRight, Activity, AlertCircle, ShoppingBag, FileText, TrendingUp, CreditCard } from '@lucide/vue';

interface Props {
    metrics: {
        total_orders: number;
        orders_in_transit: number;
        delivered_orders: number;
        total_invoiced: number;
        total_paid: number;
        outstanding_invoices_count: number;
        outstanding_invoices_total: number;
        emails_to_review: number;
    };
    recentTimeline: any[];
    recentOrders: any[];
}

defineProps<Props>();

const page = usePage();
const currencySymbol = computed(() => (page.props.currencySymbol as string) || (page.props.company as any)?.currency_symbol || '$');
</script>

<template>
    <Head title="Dashboard - BAMS" />

    <div class="space-y-6">
        <AppPageHeader title="Operations Dashboard" description="Real-time vehicle metrics, VIN tracking, and automated audit logs">
            <template #actions>
                <div class="w-full sm:w-80">
                    <GlobalVinSearch />
                </div>
            </template>
        </AppPageHeader>
        
        <!-- High-Priority Email Review Callout Banner -->
        <div
            v-if="metrics.emails_to_review > 0"
            class="p-5 sm:p-6 rounded-3xl bg-amber-500/10 dark:bg-amber-950/40 backdrop-blur-xl border border-amber-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs"
        >
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold shrink-0 shadow-xs mt-0.5 sm:mt-0">
                    <AlertCircle class="w-5 h-5 animate-pulse" />
                </div>
                <div class="space-y-0.5">
                    <h4 class="text-sm sm:text-base font-extrabold text-amber-950 dark:text-amber-300 flex items-center gap-2">
                        <span>{{ metrics.emails_to_review }} Unlinked Order Email(s) Require Review</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    </h4>
                    <p class="text-xs text-amber-900/80 dark:text-amber-400/90 leading-relaxed max-w-2xl">
                        Incoming ocean line manifests and dock receipts arrived without auto-matched VIN headers. Review and assign them to orders.
                    </p>
                </div>
            </div>
            <Link
                href="/emails?status=needs_review"
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs transition-all shadow-md shadow-amber-500/20 text-center shrink-0 active:scale-95 flex items-center justify-center gap-2"
            >
                <span>Review Inbox</span>
                <ArrowRight class="w-4 h-4" />
            </Link>
        </div>

        <!-- Section 1: Operational Metrics Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- Total Orders -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Orders</span>
                    <div class="w-11 h-11 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <ShoppingBag class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.total_orders }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total registered vehicle orders</p>
            </AppCard>

            <!-- Orders In Transit -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">In Transit</span>
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <Car class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.orders_in_transit }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Active shipped vehicle orders</p>
            </AppCard>

            <!-- Delivered Orders -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Delivered</span>
                    <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <CheckCircle2 class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.delivered_orders }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Completed order deliveries</p>
            </AppCard>

            <!-- Emails to Review -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Emails Inbox</span>
                    <div class="w-11 h-11 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <Mail class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.emails_to_review }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Unlinked communications</p>
            </AppCard>
        </div>

        <!-- Section 2: Invoicing & Financial Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
            <!-- Total Invoiced Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Invoiced</span>
                    <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <FileText class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 font-mono tracking-tight">
                    {{ currencySymbol }}{{ metrics.total_invoiced.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total value of generated invoices</p>
            </AppCard>

            <!-- Total Paid Balance Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Paid</span>
                    <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-emerald-600 dark:text-emerald-400 mb-1 font-mono tracking-tight">
                    {{ currencySymbol }}{{ metrics.total_paid.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total payments received to date</p>
            </AppCard>

            <!-- Outstanding / Unpaid Balance Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unpaid Balance</span>
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <DollarSign class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-amber-600 dark:text-amber-400 mb-1 font-mono tracking-tight">
                    {{ currencySymbol }}{{ metrics.outstanding_invoices_total.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ metrics.outstanding_invoices_count }} invoice(s) pending payment</p>
            </AppCard>
        </div>

        <!-- Responsive Layout: Recent Orders & Operational Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Vehicle Orders -->
            <AppCard class="lg:col-span-2" title="Recent Vehicle Orders" description="Latest registered orders">
                <template #headerActions>
                    <Link href="/orders" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        <span>View All Orders</span>
                        <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </template>

                <div class="overflow-x-auto custom-scrollbar -mx-6 px-6">
                    <table class="w-full text-left text-sm whitespace-nowrap min-w-[640px]">
                        <thead class="text-[11px] uppercase font-extrabold text-slate-400 border-b border-slate-100 dark:border-slate-800/80 pb-2">
                            <tr>
                                <th class="px-4 py-3 min-w-[100px]">Order #</th>
                                <th class="px-4 py-3 min-w-[180px]">VIN / Vehicle Specs</th>
                                <th class="px-4 py-3 min-w-[140px]">Customer</th>
                                <th class="px-4 py-3 min-w-[130px]">Status</th>
                                <th class="px-4 py-3 text-right min-w-[110px]">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-blue-50/40 dark:hover:bg-blue-950/30 transition-colors">
                                <td class="px-4 py-3.5 font-bold text-blue-600 dark:text-blue-400">
                                    <Link :href="`/orders/${order.id}`" class="hover:underline">{{ order.order_number }}</Link>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-mono text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">{{ order.vin }}</div>
                                    <div class="text-xs text-slate-400 font-medium">{{ order.year ? order.year + ' ' : '' }}{{ order.make || '' }} {{ order.model || '' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-semibold text-xs">
                                    {{ order.customer?.name || 'N/A' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <AppBadge :status="order.status" size="sm" />
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <Link
                                        :href="`/orders/${order.id}`"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 transition-colors shrink-0"
                                    >
                                        <span>Workspace</span>
                                        <ArrowRight class="w-3.5 h-3.5" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!recentOrders || recentOrders.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400 italic text-xs">No orders recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>

            <!-- Activity Stream -->
            <AppCard title="Operational Stream" description="Chronological audit log of operations">
                <div class="space-y-4 max-h-[390px] overflow-y-auto custom-scrollbar pr-1 relative">
                    <!-- Vertical Connector Line -->
                    <div class="absolute left-4 top-3 bottom-3 w-0.5 bg-slate-200 dark:bg-slate-800 -z-0"></div>

                    <div v-for="evt in recentTimeline" :key="evt.id" class="flex gap-3 text-xs relative z-10">
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-slate-900 border border-blue-500/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <Clock class="w-4 h-4" />
                        </div>
                        <div class="flex-1 bg-slate-50/80 dark:bg-slate-950/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800/80 space-y-1 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ evt.title }}</span>
                                <span class="text-[10px] font-semibold text-slate-400 shrink-0">{{ new Date(evt.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-xs">{{ evt.description }}</p>
                            <div v-if="evt.order" class="pt-1">
                                <Link :href="`/orders/${evt.order.id}`" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                    <span>Order {{ evt.order.order_number }} (VIN: {{ evt.order.vin.substring(0, 8) }}...)</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-if="!recentTimeline || recentTimeline.length === 0" class="py-8 text-center text-slate-400 italic text-xs">
                        No activity logged yet.
                    </div>
                </div>
            </AppCard>
        </div>
    </div>
</template>
