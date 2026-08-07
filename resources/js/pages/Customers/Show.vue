<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { User, Phone, Mail, MapPin, Car, ArrowRight, SquarePen, Search, ShieldCheck } from '@lucide/vue';

interface Props {
    customer: any;
}

const props = defineProps<Props>();

// Edit Customer Modal
const showEditModal = ref(false);
const editForm = useForm({
    name: props.customer.name || '',
    phone: props.customer.phone || '',
    whatsapp: props.customer.whatsapp || '',
    email: props.customer.email || '',
    address: props.customer.address || '',
    notes: props.customer.notes || '',
});

const openEditModal = () => {
    editForm.name = props.customer.name || '';
    editForm.phone = props.customer.phone || '';
    editForm.whatsapp = props.customer.whatsapp || '';
    editForm.email = props.customer.email || '';
    editForm.address = props.customer.address || '';
    editForm.notes = props.customer.notes || '';
    showEditModal.value = true;
};

const submitEditCustomer = () => {
    editForm.patch(`/customers/${props.customer.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            showToast.success('Customer profile updated successfully.');
        },
    });
};

// Search Filter for Vehicle Orders
const orderSearchQuery = ref('');

const filteredOrders = computed(() => {
    const orders = props.customer.orders || [];
    if (!orderSearchQuery.value.trim()) return orders;
    const query = orderSearchQuery.value.toLowerCase().trim();

    return orders.filter((ord: any) => {
        const orderNum = (ord.order_number || '').toLowerCase();
        const vin = (ord.vin || '').toLowerCase();
        const make = (ord.make || '').toLowerCase();
        const model = (ord.model || '').toLowerCase();
        const year = String(ord.year || '');
        const status = (ord.status || '').toLowerCase();

        return (
            orderNum.includes(query) ||
            vin.includes(query) ||
            make.includes(query) ||
            model.includes(query) ||
            year.includes(query) ||
            status.includes(query)
        );
    });
});
</script>

<template>
    <Head :title="`Customer: ${customer.name}`" />

    <div class="space-y-6">
        <AppPageHeader :title="customer.name" description="Customer Profile, Contact Information, and Vehicle Orders">
            <template #actions>
                <AppButton variant="secondary" @click="openEditModal">
                    <SquarePen class="w-4 h-4" /> Edit Customer
                </AppButton>
            </template>
        </AppPageHeader>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Details Card -->
            <AppCard title="Contact Profile">
                <template #headerActions>
                    <button
                        @click="openEditModal"
                        class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline flex items-center gap-1 cursor-pointer"
                    >
                        <SquarePen class="w-3.5 h-3.5" /> Edit Profile
                    </button>
                </template>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xl shadow-xs">
                            {{ customer.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ customer.name }}</h3>
                            <p class="text-xs text-slate-400">Customer ID #{{ customer.id }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-700/60 text-sm">
                        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <Phone class="w-4 h-4 text-blue-500 shrink-0" />
                            <span>{{ customer.phone }}</span>
                        </div>
                        <div v-if="customer.whatsapp" class="flex items-center gap-2 text-emerald-600 font-medium">
                            <Phone class="w-4 h-4 shrink-0" />
                            <span>WhatsApp: {{ customer.whatsapp }}</span>
                        </div>
                        <div v-if="customer.email" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <Mail class="w-4 h-4 text-blue-500 shrink-0" />
                            <span>{{ customer.email }}</span>
                        </div>
                        <div v-if="customer.address" class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <MapPin class="w-4 h-4 text-blue-500 shrink-0" />
                            <span>{{ customer.address }}</span>
                        </div>
                    </div>

                    <div v-if="customer.notes" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        <strong class="text-slate-900 dark:text-white">Internal Notes:</strong> {{ customer.notes }}
                    </div>
                </div>
            </AppCard>

            <!-- Customer Vehicle Orders List with Live Search & Scrollable Filter -->
            <AppCard
                class="lg:col-span-2"
                title="Vehicle Orders"
                :description="`${customer.orders?.length || 0} total order(s) registered`"
            >
                <template #headerActions>
                    <!-- Live Search Bar for Orders -->
                    <div v-if="customer.orders && customer.orders.length > 0" class="relative w-full sm:w-64">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" />
                        <input
                            type="text"
                            v-model="orderSearchQuery"
                            placeholder="Filter VIN, order #, model..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>
                </template>

                <div v-if="!customer.orders || customer.orders.length === 0" class="py-12 text-center text-slate-400 text-sm">
                    <Car class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                    No orders created for this customer yet.
                </div>

                <div v-else-if="filteredOrders.length === 0" class="py-8 text-center text-slate-400 text-sm">
                    No vehicle orders match your search criteria "{{ orderSearchQuery }}".
                </div>

                <div v-else class="space-y-3 max-h-[550px] overflow-y-auto pr-1">
                    <div
                        v-for="ord in filteredOrders"
                        :key="ord.id"
                        class="p-4 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900 hover:border-blue-300 dark:hover:border-blue-700 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                <Car class="w-5 h-5" />
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <Link :href="`/orders/${ord.id}`" class="font-extrabold text-sm text-blue-600 hover:underline">{{ ord.order_number }}</Link>
                                    <AppBadge :status="ord.status" size="sm" />
                                </div>
                                <div class="flex items-center gap-2 font-mono text-xs text-slate-700 dark:text-slate-300">
                                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" />
                                    <span>VIN: <strong class="text-blue-600 dark:text-blue-400">{{ ord.vin }}</strong></span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ ord.year ? ord.year + ' ' : '' }}{{ ord.make }} {{ ord.model }}
                                    <span v-if="ord.destination" class="ml-2 text-slate-400">| Port: {{ ord.destination }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800">
                            <div v-if="ord.invoice" class="text-right text-xs">
                                <span class="text-slate-400 block">Balance</span>
                                <span class="font-bold text-slate-900 dark:text-white">${{ Number(ord.invoice.balance || 0).toFixed(2) }}</span>
                            </div>
                            <Link
                                :href="`/orders/${ord.id}`"
                                class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition-colors flex items-center gap-1 shrink-0"
                            >
                                Open Workspace <ArrowRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </AppCard>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <AppModal :show="showEditModal" title="Edit Customer Details" @close="showEditModal = false">
        <form @submit.prevent="submitEditCustomer" class="space-y-4">
            <AppFormField label="Full Name" required :error="editForm.errors.name">
                <AppInput v-model="editForm.name" placeholder="John Doe" />
            </AppFormField>
            <div class="grid grid-cols-2 gap-3">
                <AppFormField label="Phone Number" required :error="editForm.errors.phone">
                    <AppInput v-model="editForm.phone" placeholder="+1 555-0192" />
                </AppFormField>
                <AppFormField label="WhatsApp Number" :error="editForm.errors.whatsapp">
                    <AppInput v-model="editForm.whatsapp" placeholder="+1 555-0192" />
                </AppFormField>
            </div>
            <AppFormField label="Email Address" :error="editForm.errors.email">
                <AppInput v-model="editForm.email" placeholder="john@example.com" />
            </AppFormField>
            <AppFormField label="Physical Address" :error="editForm.errors.address">
                <AppTextarea v-model="editForm.address" placeholder="123 Palm Street, Houston TX" :rows="2" />
            </AppFormField>
            <AppFormField label="Notes" :error="editForm.errors.notes">
                <AppTextarea v-model="editForm.notes" placeholder="VIP Client..." :rows="2" />
            </AppFormField>
            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showEditModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="editForm.processing">Save Profile Changes</AppButton>
            </div>
        </form>
    </AppModal>
</template>
