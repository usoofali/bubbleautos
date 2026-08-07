<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import AppTable from '@/components/common/AppTable.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Plus, Search, User, Phone, Mail, ArrowRight, Trash2, SquarePen } from '@lucide/vue';

interface Props {
    customers: any;
    filters: { search: string };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');

const filterCustomers = () => {
    router.get('/customers', { search: searchQuery.value }, { preserveState: true, replace: true });
};

// Create / Edit Customer Modal
const showCreateModal = ref(false);
const editingCustomer = ref<any>(null);

const customerForm = useForm({
    name: '',
    phone: '',
    whatsapp: '',
    email: '',
    address: '',
    notes: '',
});

const openCreateModal = () => {
    editingCustomer.value = null;
    customerForm.reset();
    showCreateModal.value = true;
};

const openEditModal = (customer: any) => {
    editingCustomer.value = customer;
    customerForm.name = customer.name || '';
    customerForm.phone = customer.phone || '';
    customerForm.whatsapp = customer.whatsapp || '';
    customerForm.email = customer.email || '';
    customerForm.address = customer.address || '';
    customerForm.notes = customer.notes || '';
    showCreateModal.value = true;
};

const submitCustomer = () => {
    if (editingCustomer.value) {
        customerForm.patch(`/customers/${editingCustomer.value.id}`, {
            onSuccess: () => {
                showCreateModal.value = false;
                editingCustomer.value = null;
                customerForm.reset();
                showToast.success('Customer details updated successfully.');
            },
        });
    } else {
        customerForm.post('/customers', {
            onSuccess: () => {
                showCreateModal.value = false;
                customerForm.reset();
                showToast.success('Customer registered successfully.');
            },
        });
    }
};

// Delete Customer Confirm Modal
const deletingCustomer = ref<any>(null);
const confirmDeleteCustomer = () => {
    if (!deletingCustomer.value) return;
    router.delete(`/customers/${deletingCustomer.value.id}`, {
        onSuccess: () => {
            deletingCustomer.value = null;
            showToast.success('Customer soft deleted.');
        },
    });
};
</script>

<template>
    <Head title="Customers - BAMS" />

    <div class="space-y-6">
        <AppPageHeader title="Customer Directory" description="Manage Bubble Autos clients, order history, and account balances">
            <template #actions>
                <AppButton variant="primary" @click="openCreateModal">
                    <Plus class="w-4 h-4" /> Add New Customer
                </AppButton>
            </template>
        </AppPageHeader>

        <AppCard no-padding class="p-4">
            <div class="relative w-full sm:w-80">
                <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                    type="text"
                    v-model="searchQuery"
                    @keyup.enter="filterCustomers"
                    placeholder="Search name, phone, or email..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"
                />
            </div>
        </AppCard>

        <AppTable
            :columns="[
                { key: 'name', label: 'Customer Name' },
                { key: 'phone', label: 'Phone / WhatsApp' },
                { key: 'email', label: 'Email' },
                { key: 'orders_count', label: 'Total Orders' },
                { key: 'actions', label: 'Actions', align: 'right' },
            ]"
            :items="customers.data"
            empty-title="No Customers Found"
            empty-description="Create your first customer or adjust your search filters."
        >
            <template #rows="{ items }">
                <tr v-for="c in items" :key="c.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                        <Link :href="`/customers/${c.id}`" class="hover:text-blue-600">{{ c.name }}</Link>
                    </td>
                    <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                        <div>{{ c.phone }}</div>
                        <div v-if="c.whatsapp" class="text-xs text-emerald-600">WA: {{ c.whatsapp }}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                        {{ c.email || '-' }}
                    </td>
                    <td class="px-6 py-4 font-bold">
                        {{ c.orders_count || 0 }} order(s)
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button
                                @click="openEditModal(c)"
                                class="p-1.5 text-blue-600 hover:text-blue-800 rounded-lg hover:bg-blue-50 transition-colors"
                                title="Edit Customer"
                            >
                                <SquarePen class="w-4 h-4" />
                            </button>
                            <Link
                                :href="`/customers/${c.id}`"
                                class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition-colors flex items-center gap-1"
                            >
                                View Profile <ArrowRight class="w-3.5 h-3.5" />
                            </Link>
                            <button
                                v-if="$page.props.auth.user.is_admin"
                                @click="deletingCustomer = c"
                                class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                title="Delete Customer"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            </template>
        </AppTable>

        <AppPagination :links="customers.links" />
    </div>

    <!-- Create / Edit Customer Modal -->
    <AppModal :show="showCreateModal" :title="editingCustomer ? 'Edit Customer Details' : 'Register New Customer'" @close="showCreateModal = false">
        <form @submit.prevent="submitCustomer" class="space-y-4">
            <AppFormField label="Full Name" required :error="customerForm.errors.name">
                <AppInput v-model="customerForm.name" placeholder="John Doe" />
            </AppFormField>
            <div class="grid grid-cols-2 gap-3">
                <AppFormField label="Phone Number" required :error="customerForm.errors.phone">
                    <AppInput v-model="customerForm.phone" placeholder="+1 555-0192" />
                </AppFormField>
                <AppFormField label="WhatsApp Number" :error="customerForm.errors.whatsapp">
                    <AppInput v-model="customerForm.whatsapp" placeholder="+1 555-0192" />
                </AppFormField>
            </div>
            <AppFormField label="Email Address" :error="customerForm.errors.email">
                <AppInput v-model="customerForm.email" placeholder="john@example.com" />
            </AppFormField>
            <AppFormField label="Physical Address" :error="customerForm.errors.address">
                <AppTextarea v-model="customerForm.address" placeholder="123 Palm Street, Houston TX" :rows="2" />
            </AppFormField>
            <AppFormField label="Notes" :error="customerForm.errors.notes">
                <AppTextarea v-model="customerForm.notes" placeholder="VIP Client..." :rows="2" />
            </AppFormField>
            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showCreateModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="customerForm.processing">
                    {{ editingCustomer ? 'Save Customer Changes' : 'Create Customer' }}
                </AppButton>
            </div>
        </form>
    </AppModal>

    <ConfirmModal
        :show="!!deletingCustomer"
        title="Delete Customer Record?"
        message="Are you sure you want to soft delete this customer? Orders associated with this customer will remain in the database."
        @close="deletingCustomer = null"
        @confirm="confirmDeleteCustomer"
    />
</template>
