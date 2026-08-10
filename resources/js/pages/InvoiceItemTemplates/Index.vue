<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Plus, Receipt, SquarePen, Trash2 } from '@lucide/vue';

interface Template {
    id: number;
    description: string;
    default_amount: number | string;
    created_at: string;
    updated_at: string;
}

interface Props {
    templates: Template[];
}

const props = defineProps<Props>();

const page = usePage();
const currencySymbol = computed(() => (page.props as any).currencySymbol || '$');
const currencyCode = computed(() => (page.props as any).currencyCode || 'USD');

// Create Modal & Form
const showCreateModal = ref(false);
const createForm = useForm({
    description: '',
    default_amount: '',
});

const submitCreate = () => {
    createForm.post('/invoice-item-templates', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            showToast.success('Preset invoice item created successfully.');
        },
    });
};

// Edit Modal & Form
const editingTemplate = ref<Template | null>(null);
const editForm = useForm({
    description: '',
    default_amount: '',
});

const openEditModal = (t: Template) => {
    editingTemplate.value = t;
    editForm.description = t.description;
    editForm.default_amount = String(t.default_amount);
};

const submitEdit = () => {
    if (!editingTemplate.value) return;
    editForm.patch(`/invoice-item-templates/${editingTemplate.value.id}`, {
        onSuccess: () => {
            editingTemplate.value = null;
            editForm.reset();
            showToast.success('Preset invoice item updated successfully.');
        },
    });
};

// Delete Modal & Form
const deletingTemplate = ref<Template | null>(null);
const confirmDelete = () => {
    if (!deletingTemplate.value) return;
    router.delete(`/invoice-item-templates/${deletingTemplate.value.id}`, {
        onSuccess: () => {
            deletingTemplate.value = null;
            showToast.success('Preset invoice item deleted successfully.');
        },
    });
};
</script>

<template>
    <Head title="Preset Invoice Items" />

    <div class="space-y-6">
        <AppPageHeader
            title="Preset Invoice Items Catalog"
            description="Manage predefined invoice line items and default amounts for consistent billing across all vehicle orders."
        >
            <template #actions>
                <AppButton variant="primary" @click="showCreateModal = true">
                    <Plus class="w-4 h-4" />
                    <span>Add Preset Item</span>
                </AppButton>
            </template>
        </AppPageHeader>

        <!-- Master Item Table Card -->
        <AppCard title="Predefined Item Catalog" description="Standardized billing items available to staff during invoice creation">
            <template #headerActions>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <Receipt class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                    <span>Total Items: {{ props.templates ? props.templates.length : 0 }}</span>
                </div>
            </template>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-xs uppercase font-semibold text-slate-400 border-b border-slate-200/80 dark:border-slate-700/60">
                        <tr>
                            <th class="py-3 px-4">Item Description</th>
                            <th class="py-3 px-4 text-right">Default Amount ({{ currencyCode }})</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="t in props.templates" :key="t.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                {{ t.description }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                {{ currencySymbol }}{{ Number(t.default_amount).toFixed(2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openEditModal(t)"
                                        class="p-1.5 rounded-lg text-blue-600 hover:text-blue-800 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
                                        title="Edit Item"
                                    >
                                        <SquarePen class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="deletingTemplate = t"
                                        class="p-1.5 rounded-lg text-red-500 hover:text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                        title="Delete Item"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.templates || props.templates.length === 0">
                            <td colspan="3" class="py-8 text-center text-slate-400 text-xs italic">
                                No preset invoice items created yet. Click "Add Preset Item" to create your first item.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </AppCard>
    </div>

    <!-- Create Preset Item Modal -->
    <AppModal :show="showCreateModal" title="Add Preset Invoice Item" @close="showCreateModal = false">
        <form @submit.prevent="submitCreate" class="space-y-4">
            <AppFormField label="Item Description" required :error="createForm.errors.description">
                <AppInput v-model="createForm.description" placeholder="e.g. Ocean Freight Charge" />
            </AppFormField>

            <AppFormField label="Default Amount (USD)" required :error="createForm.errors.default_amount">
                <AppInput type="number" v-model="createForm.default_amount" placeholder="0.00" step="0.01" min="0" />
            </AppFormField>

            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="showCreateModal = false">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="createForm.processing">Save Preset Item</AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Edit Preset Item Modal -->
    <AppModal :show="!!editingTemplate" title="Edit Preset Invoice Item" @close="editingTemplate = null">
        <form @submit.prevent="submitEdit" class="space-y-4">
            <AppFormField label="Item Description" required :error="editForm.errors.description">
                <AppInput v-model="editForm.description" placeholder="e.g. Ocean Freight  Charge" />
            </AppFormField>

            <AppFormField label="Default Amount (USD)" required :error="editForm.errors.default_amount">
                <AppInput type="number" v-model="editForm.default_amount" placeholder="0.00" step="0.01" min="0" />
            </AppFormField>

            <div class="flex justify-end gap-3 pt-2">
                <AppButton variant="outline" @click="editingTemplate = null">Cancel</AppButton>
                <AppButton type="submit" variant="primary" :loading="editForm.processing">Update Preset Item</AppButton>
            </div>
        </form>
    </AppModal>

    <!-- Delete Confirm Modal -->
    <ConfirmModal
        :show="!!deletingTemplate"
        title="Delete Preset Invoice Item"
        :message="`Are you sure you want to delete preset item '${deletingTemplate?.description}'? This action cannot be undone.`"
        confirmLabel="Delete Item"
        confirmVariant="danger"
        @confirm="confirmDelete"
        @close="deletingTemplate = null"
    />
</template>
