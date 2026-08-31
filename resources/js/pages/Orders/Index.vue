<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import AppTable from '@/components/common/AppTable.vue';
import FileUploader from '@/components/common/FileUploader.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Plus, Search, Car, ArrowRight, Trash2, FileText, ChevronDown, ChevronUp, X, Filter, ShoppingBag, Calendar, CheckCircle2 } from '@lucide/vue';

interface Props {
    orders: any;
    customers: any[];
    filters: { search: string; status: string };
    statusOptions: Array<{ value: string; label: string }>;
    stats?: {
        total: number;
        this_month: number;
        in_transit: number;
        delivered: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');

const filterOrders = () => {
    router.get('/orders', { search: searchQuery.value, status: selectedStatus.value }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    filterOrders();
};

// Create Order Modal
const showCreateModal = ref(false);
const createNewCustomer = ref(false);
const showOptionalFields = ref(false);

const orderForm = useForm({
    customer_id: '' as string | number,
    new_customer: {
        name: '',
        phone: '',
        email: '',
    },
    vin: '',
    auction_receipt: null as File | null,
    make: '',
    model: '',
    year: '' as string | number,
    color: '',
    shipping_line: '',
    destination: '',
    expected_arrival: '',
});

const submitCreateOrder = () => {
    orderForm
        .transform((data) => ({
            ...data,
            customer_id: createNewCustomer.value ? null : (data.customer_id || null),
            new_customer: createNewCustomer.value && data.new_customer?.name ? data.new_customer : null,
        }))
        .post('/orders', {
            onSuccess: () => {
                showCreateModal.value = false;
                orderForm.reset();
                createNewCustomer.value = false;
                showOptionalFields.value = false;
                showToast.success('Order registered successfully!');
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    showToast.error(firstError);
                }
            },
        });
};

// Delete Order Confirmation Modal
const deletingOrder = ref<any>(null);
const confirmDeleteOrder = () => {
    if (!deletingOrder.value) return;
    router.delete(`/orders/${deletingOrder.value.id}`, {
        onSuccess: () => {
            deletingOrder.value = null;
            showToast.success('Order deleted.');
        },
    });
};
</script>

<template>
    <Head title="Vehicle Orders - BAMS" />

    <div class="space-y-6">
        <AppPageHeader title="Vehicle Orders" description="Manage vehicle shipments, VIN numbers, and status tracking">
            <template #actions>
                <AppButton variant="primary" size="md" @click="showCreateModal = true" class="w-full sm:w-auto shadow-xs">
                    <Plus class="w-4 h-4" /> Register New Order
                </AppButton>
            </template>
        </AppPageHeader>

        <!-- Metric Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <!-- Total Orders Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Orders</span>
                    <div class="w-11 h-11 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <ShoppingBag class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ stats?.total ?? orders?.total ?? 0 }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total registered vehicle orders</p>
            </AppCard>

            <!-- Orders This Month Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Orders This Month</span>
                    <div class="w-11 h-11 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <Calendar class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ stats?.this_month ?? 0 }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Orders registered in current month</p>
            </AppCard>

            <!-- In Transit Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">In Transit</span>
                    <div class="w-11 h-11 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <Car class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ stats?.in_transit ?? 0 }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Active shipments en route</p>
            </AppCard>

            <!-- Delivered Card -->
            <AppCard no-padding hoverable class="p-5 sm:p-6 group border-slate-200/80 dark:border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Delivered</span>
                    <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-2xs group-hover:scale-110 transition-transform">
                        <CheckCircle2 class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-1 tracking-tight">
                    {{ stats?.delivered ?? 0 }}
                </div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Completed vehicle deliveries</p>
            </AppCard>
        </div>

        <!-- Search & Filters Bar (Mobile-First Layout) -->
        <AppCard no-padding class="p-4 sm:p-5 shadow-xs border-slate-200/80 dark:border-slate-800/80">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 justify-between">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @keyup.enter="filterOrders"
                        placeholder="Search VIN, Order #, Customer..."
                        class="w-full pl-10 pr-9 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-hidden font-medium transition-all"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <div class="relative flex-1 sm:w-60">
                        <select
                            v-model="selectedStatus"
                            @change="filterOrders"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-hidden font-semibold transition-all"
                        >
                            <option value="">All Shipment Statuses</option>
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </div>
                    <AppButton variant="secondary" size="md" @click="filterOrders" class="shrink-0 rounded-xl font-bold">
                        <Filter class="w-4 h-4" /> Filter
                    </AppButton>
                </div>
            </div>
        </AppCard>

        <!-- Orders Data Table & Mobile List -->
        <AppTable
            :columns="[
                { key: 'order_number', label: 'Order #' },
                { key: 'vin', label: 'VIN / Vehicle Specs' },
                { key: 'customer', label: 'Customer' },
                { key: 'destination', label: 'Destination' },
                { key: 'status', label: 'Status' },
                { key: 'actions', label: 'Actions', align: 'right' },
            ]"
            :items="orders.data"
            empty-title="No Vehicle Orders Found"
            empty-description="Register a new order or adjust your search filters."
        >
            <template #rows="{ items }">
                <tr v-for="ord in items" :key="ord.id" class="hover:bg-blue-50/40 dark:hover:bg-blue-950/30 transition-colors">
                    <td class="px-4 sm:px-6 py-4 font-black text-blue-600 dark:text-blue-400 whitespace-nowrap min-w-[120px]">
                        <Link :href="`/orders/${ord.id}`" class="hover:underline font-mono tracking-tight shrink-0 whitespace-nowrap">{{ ord.order_number }}</Link>
                    </td>
                    <td class="px-4 sm:px-6 py-4 min-w-[180px]">
                        <div class="font-mono text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide whitespace-nowrap">{{ ord.vin }}</div>
                        <div class="text-xs text-slate-400 font-medium whitespace-nowrap">
                            <span v-if="ord.make || ord.model">{{ ord.year ? ord.year + ' ' : '' }}{{ ord.make }} {{ ord.model }}</span>
                            <span v-else class="italic text-slate-400">Pending API Lookup</span>
                        </div>
                    </td>
                    <td class="px-4 sm:px-6 py-4 min-w-[140px]">
                        <div class="font-extrabold text-slate-900 dark:text-white text-xs whitespace-nowrap">{{ ord.customer?.name || 'N/A' }}</div>
                        <div class="text-xs text-slate-400 font-mono whitespace-nowrap">{{ ord.customer?.phone }}</div>
                    </td>
                    <td class="px-4 sm:px-6 py-4 min-w-[150px]">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ ord.destination || 'Pending Sync' }}</div>
                        <div class="text-xs text-slate-400 font-medium whitespace-nowrap">{{ ord.shipping_line || 'Pending Line' }}</div>
                    </td>
                    <td class="px-4 sm:px-6 py-4 min-w-[120px]">
                        <AppBadge :status="ord.status" size="sm" />
                    </td>
                    <td class="px-4 sm:px-6 py-4 text-right min-w-[110px]">
                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                            <Link
                                :href="`/orders/${ord.id}`"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-extrabold text-xs bg-blue-600 hover:bg-blue-500 text-white shadow-xs active:scale-95 transition-all shrink-0"
                            >
                                <span>Workspace</span>
                                <ArrowRight class="w-3.5 h-3.5" />
                            </Link>
                            <button
                                v-if="$page.props.auth.user.is_admin"
                                @click="deletingOrder = ord"
                                class="p-1.5 text-slate-400 hover:text-red-600 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors shrink-0"
                                title="Delete Order"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </AppTable>

        <AppPagination :links="orders.links" />
    </div>

    <!-- Register Order Modal -->
    <AppModal :show="showCreateModal" title="Register New Vehicle Order" maxWidth="lg" @close="showCreateModal = false">
        <form @submit.prevent="submitCreateOrder" class="space-y-4">
            <!-- Customer Selection Section -->
            <div class="space-y-2">
                <div class="flex items-center justify-between border-b pb-2 dark:border-slate-700">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">1. Customer Information</span>
                    <button
                        type="button"
                        @click="createNewCustomer = !createNewCustomer"
                        class="text-xs font-bold text-blue-600 hover:underline"
                    >
                        {{ createNewCustomer ? 'Select Existing Customer' : '+ New Customer' }}
                    </button>
                </div>

                <div v-if="!createNewCustomer">
                    <AppFormField label="Select Customer" required :error="orderForm.errors.customer_id">
                        <AppSelect v-model="orderForm.customer_id" placeholder="Choose registered customer...">
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </AppSelect>
                    </AppFormField>
                </div>

                <div v-else class="space-y-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border dark:border-slate-700">
                    <AppFormField label="Customer Full Name" required :error="orderForm.errors['new_customer.name']">
                        <AppInput v-model="orderForm.new_customer.name" placeholder="e.g. John Doe" />
                    </AppFormField>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <AppFormField label="Phone Number" required :error="orderForm.errors['new_customer.phone']">
                            <AppInput v-model="orderForm.new_customer.phone" placeholder="+1 555-0192" />
                        </AppFormField>
                        <AppFormField label="Email Address (Optional)" :error="orderForm.errors['new_customer.email']">
                            <AppInput v-model="orderForm.new_customer.email" placeholder="customer@example.com" />
                        </AppFormField>
                    </div>
                </div>
            </div>

            <!-- Vehicle VIN Section -->
            <div class="space-y-2 pt-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block border-b pb-2 dark:border-slate-700">2. Vehicle Information</span>
                
                <AppFormField label="17-Digit VIN" required :error="orderForm.errors.vin">
                    <AppInput v-model="orderForm.vin" placeholder="1FA6P8CF0H5123456" class="font-mono uppercase" maxlength="17" />
                </AppFormField>

                <!-- Optional Details Accordion Toggle -->
                <button
                    type="button"
                    @click="showOptionalFields = !showOptionalFields"
                    class="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-blue-600 flex items-center gap-1.5 pt-1"
                >
                    <component :is="showOptionalFields ? ChevronUp : ChevronDown" class="w-4 h-4" />
                    {{ showOptionalFields ? 'Hide Optional Vehicle Specs' : 'Add Vehicle Details (Make, Model, Year, Line...)' }}
                </button>

                <div v-if="showOptionalFields" class="space-y-3 pt-2">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <AppFormField label="Make" :error="orderForm.errors.make">
                            <AppInput v-model="orderForm.make" placeholder="Ford" />
                        </AppFormField>
                        <AppFormField label="Model" :error="orderForm.errors.model">
                            <AppInput v-model="orderForm.model" placeholder="Mustang" />
                        </AppFormField>
                        <AppFormField label="Year" :error="orderForm.errors.year">
                            <AppInput type="number" v-model="orderForm.year" placeholder="2022" />
                        </AppFormField>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <AppFormField label="Shipping Line" :error="orderForm.errors.shipping_line">
                            <AppSelect v-model="orderForm.shipping_line" placeholder="Select line...">
                                <option value="Sallaum Lines">Sallaum Lines</option>
                                <option value="Grimaldi Lines">Grimaldi Lines</option>
                                <option value="MSC Line">MSC Line</option>
                            </AppSelect>
                        </AppFormField>
                        <AppFormField label="Destination Port" :error="orderForm.errors.destination">
                            <AppSelect v-model="orderForm.destination" placeholder="Select destination...">
                                <option value="Lagos, Nigeria">Lagos, Nigeria</option>
                                <option value="Cotonou, Benin">Cotonou, Benin</option>
                            </AppSelect>
                        </AppFormField>
                    </div>
                </div>
            </div>

            <!-- Initial Document Attachment -->
            <div class="space-y-2 pt-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block border-b pb-2 dark:border-slate-700">3. Document Attachment (Optional)</span>
                <AppFormField label="Auction Receipt / Gate Pass" :error="orderForm.errors.auction_receipt">
                    <FileUploader
                        v-model="orderForm.auction_receipt"
                        @file-selected="(file: File | null) => (orderForm.auction_receipt = file)"
                        @file-removed="() => (orderForm.auction_receipt = null)"
                    />
                </AppFormField>
            </div>

            <div class="flex justify-end gap-3 pt-3">
                <AppButton variant="outline" @click="showCreateModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="orderForm.processing">Register Order</AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
        :show="!!deletingOrder"
        title="Permanently Delete Vehicle Order"
        :message="`Are you sure you want to permanently delete order ${deletingOrder?.order_number}? This action cannot be undone.`"
        confirm-text="Permanently Delete Order"
        variant="danger"
        @close="deletingOrder = null"
        @confirm="confirmDeleteOrder"
    />
</template>
