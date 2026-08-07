<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import GlobalVinSearch from '@/components/common/GlobalVinSearch.vue';
import { Car, CheckCircle2, DollarSign, Mail, Clock, ArrowRight, Activity, AlertCircle } from '@lucide/vue';

interface Props {
    metrics: {
        orders_in_transit: number;
        delivered_orders: number;
        outstanding_invoices_count: number;
        outstanding_invoices_total: number;
        emails_to_review: number;
    };
    recentTimeline: any[];
    recentOrders: any[];
}

defineProps<Props>();
</script>

<template>
    <Head title="Dashboard - BAMS" />

    <div class="space-y-6">
        <AppPageHeader title="Dashboard" description="Bubble Autos Management System">
            <template #actions>
                <div class="w-full sm:w-auto">
                    <GlobalVinSearch />
                </div>
            </template>
        </AppPageHeader>
        
        <!-- High-Priority Email Review Callout Banner -->
        <div
            v-if="metrics.emails_to_review > 0"
            class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
        >
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold shrink-0 shadow-xs mt-0.5 sm:mt-0">
                    <AlertCircle class="w-5 h-5 animate-pulse" />
                </div>
                <div class="space-y-0.5">
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-300 flex items-center gap-2">
                        <span>{{ metrics.emails_to_review }} Shipping Email(s) Require Manual Review</span>
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    </h4>
                    <p class="text-xs text-amber-800/80 dark:text-amber-400/90 leading-relaxed">
                        Incoming emails arrived without auto-matching VIN headers. Review and link them to vehicle orders.
                    </p>
                </div>
            </div>
            <Link
                href="/emails?status=needs_review"
                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs transition-all shadow-xs text-center shrink-0 active:scale-95 flex items-center justify-center gap-1.5"
            >
                Review Inbox Emails <ArrowRight class="w-3.5 h-3.5" />
            </Link>
        </div>

        <!-- Mobile-First Responsive Metrics Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- Orders In Transit -->
            <AppCard no-padding class="p-5 hover:-translate-y-0.5 transition-all duration-200 shadow-xs border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">In Transit</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-xs">
                        <Car class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.orders_in_transit }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Active shipped vehicle orders</p>
            </AppCard>

            <!-- Delivered Orders -->
            <AppCard no-padding class="p-5 hover:-translate-y-0.5 transition-all duration-200 shadow-xs border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Delivered</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-xs">
                        <CheckCircle2 class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.delivered_orders }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Completed order deliveries</p>
            </AppCard>

            <!-- Outstanding Invoices -->
            <AppCard no-padding class="p-5 hover:-translate-y-0.5 transition-all duration-200 shadow-xs border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Unpaid Balance</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-xs">
                        <DollarSign class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-1 font-mono tracking-tight">
                    ${{ metrics.outstanding_invoices_total.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ metrics.outstanding_invoices_count }} invoice(s) pending payment</p>
            </AppCard>

            <!-- Emails to Review -->
            <AppCard no-padding class="p-5 hover:-translate-y-0.5 transition-all duration-200 shadow-xs border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Emails Inbox</span>
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shadow-xs">
                        <Mail class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ metrics.emails_to_review }}
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Unlinked communications</p>
            </AppCard>
        </div>

        <!-- Responsive Layout: Recent Orders & Operational Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Vehicle Orders -->
            <AppCard class="lg:col-span-2" title="Recent Vehicle Orders" description="Latest registered vehicle shipments and status updates">
                <template #headerActions>
                    <Link href="/orders" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                        View All <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </template>

                <div class="overflow-x-auto custom-scrollbar -mx-6 px-6">
                    <table class="w-full text-left text-sm whitespace-nowrap min-w-[500px]">
                        <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-2">
                            <tr>
                                <th class="py-2.5">Order #</th>
                                <th class="py-2.5">VIN / Vehicle</th>
                                <th class="py-2.5">Customer</th>
                                <th class="py-2.5">Status</th>
                                <th class="py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 font-bold text-blue-600 dark:text-blue-400">
                                    <Link :href="`/orders/${order.id}`" class="hover:underline">{{ order.order_number }}</Link>
                                </td>
                                <td class="py-3">
                                    <div class="font-mono text-xs font-bold text-slate-900 dark:text-slate-100 uppercase">{{ order.vin }}</div>
                                    <div class="text-xs text-slate-400">{{ order.year ? order.year + ' ' : '' }}{{ order.make || '' }} {{ order.model || '' }}</div>
                                </td>
                                <td class="py-3 text-slate-700 dark:text-slate-300 font-semibold">
                                    {{ order.customer?.name || 'N/A' }}
                                </td>
                                <td class="py-3">
                                    <AppBadge :status="order.status" size="sm" />
                                </td>
                                <td class="py-3 text-right">
                                    <Link
                                        :href="`/orders/${order.id}`"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400 p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
                                    >
                                        Open <ArrowRight class="w-3.5 h-3.5" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!recentOrders || recentOrders.length === 0">
                                <td colspan="5" class="py-6 text-center text-slate-400 italic text-xs">No orders recorded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </AppCard>

            <!-- Activity Stream -->
            <AppCard title="Operational Stream" description="Chronological audit log of operations">
                <div class="space-y-4 max-h-[380px] overflow-y-auto custom-scrollbar pr-1 relative">
                    <!-- Vertical Connector Line -->
                    <div class="absolute left-3.5 top-3 bottom-3 w-0.5 bg-slate-200 dark:bg-slate-800 -z-0"></div>

                    <div v-for="evt in recentTimeline" :key="evt.id" class="flex gap-3 text-xs relative z-10">
                        <div class="w-7 h-7 rounded-full bg-white dark:bg-slate-800 border border-blue-500/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                            <Clock class="w-3.5 h-3.5" />
                        </div>
                        <div class="flex-1 bg-slate-50/70 dark:bg-slate-800/40 p-3 rounded-xl border border-slate-100 dark:border-slate-800/60 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900 dark:text-white">{{ evt.title }}</span>
                                <span class="text-[10px] font-semibold text-slate-400 shrink-0">{{ new Date(evt.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 leading-relaxed">{{ evt.description }}</p>
                            <div v-if="evt.order" class="pt-1">
                                <Link :href="`/orders/${evt.order.id}`" class="text-[11px] font-bold text-blue-600 hover:underline inline-flex items-center gap-1">
                                    Order {{ evt.order.order_number }} (VIN: {{ evt.order.vin.substring(0, 8) }}...)
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
