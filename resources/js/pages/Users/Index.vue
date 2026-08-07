<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppBadge from '@/components/common/AppBadge.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppSelect from '@/components/common/AppSelect.vue';
import AppTable from '@/components/common/AppTable.vue';
import AppPagination from '@/components/common/AppPagination.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Plus, User, ShieldCheck, Power, Trash2, Edit } from '@lucide/vue';

interface Props {
    users: any;
    roles: any[];
    permissionsGrouped: Record<string, any[]>;
    filters: { search: string };
}

const props = defineProps<Props>();

// Create Staff Form
const showCreateModal = ref(false);
const userForm = useForm({
    name: '',
    email: '',
    password: '',
    role_id: props.roles[0]?.id || 1,
    is_active: true,
    direct_permissions: [] as number[],
});

const submitCreateUser = () => {
    userForm.post('/users', {
        onSuccess: () => {
            showCreateModal.value = false;
            userForm.reset();
            showToast.success('Staff account created successfully.');
        },
    });
};

// Edit Staff Form
const editingUser = ref<any>(null);
const editForm = useForm({
    name: '',
    email: '',
    role_id: 1,
    is_active: true,
    password: '',
    direct_permissions: [] as number[],
});

const openEditModal = (u: any) => {
    editingUser.value = u;
    editForm.name = u.name;
    editForm.email = u.email;
    editForm.role_id = u.role_id;
    editForm.is_active = !!u.is_active;
    editForm.password = '';
    editForm.direct_permissions = u.direct_permissions ? u.direct_permissions.map((p: any) => p.id) : [];
};

const submitEditUser = () => {
    if (!editingUser.value) return;
    editForm.patch(`/users/${editingUser.value.id}`, {
        onSuccess: () => {
            editingUser.value = null;
            showToast.success('Staff account updated successfully.');
        },
    });
};

// Toggle Active Status
const toggleActive = (u: any) => {
    router.patch(`/users/${u.id}/toggle-active`, {}, {
        onSuccess: () => {
            showToast.success(`Staff account ${u.is_active ? 'deactivated' : 'activated'}.`);
        },
    });
};

// Delete Staff Confirm Modal
const deletingUser = ref<any>(null);
const confirmDeleteUser = () => {
    if (!deletingUser.value) return;
    router.delete(`/users/${deletingUser.value.id}`, {
        onSuccess: () => {
            deletingUser.value = null;
            showToast.success('Staff account deleted.');
        },
    });
};
</script>

<template>
    <Head title="Staff Management - BAMS" />

    <div class="space-y-6">
            <AppPageHeader title="Staff Management" description="Create and manage internal Bubble Autos staff accounts, roles, and authorization permissions">
                <template #actions>
                    <AppButton variant="primary" @click="showCreateModal = true">
                        <Plus class="w-4 h-4" /> Create Staff Account
                    </AppButton>
                </template>
            </AppPageHeader>

            <AppTable
                :columns="[
                    { key: 'name', label: 'Staff Member' },
                    { key: 'role', label: 'Assigned Role' },
                    { key: 'is_active', label: 'Account Status' },
                    { key: 'actions', label: 'Actions', align: 'right' },
                ]"
                :items="users.data"
                empty-title="No Staff Accounts Found"
            >
                <template #rows="{ items }">
                    <tr v-for="u in items" :key="u.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ u.name }}</div>
                            <div class="text-xs text-slate-400">{{ u.email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ u.role?.name || 'No Role' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-bold"
                                :class="u.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'"
                            >
                                {{ u.is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="toggleActive(u)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50"
                                    :title="u.is_active ? 'Deactivate Account' : 'Activate Account'"
                                >
                                    <Power class="w-4 h-4" />
                                </button>
                                <button
                                    @click="openEditModal(u)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50"
                                    title="Edit Staff Account"
                                >
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button
                                    v-if="$page.props.auth.user.id !== u.id"
                                    @click="deletingUser = u"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50"
                                    title="Delete Account"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </AppTable>

            <AppPagination :links="users.links" />
        </div>

        <!-- Create Staff Modal -->
        <AppModal :show="showCreateModal" title="Create Staff Account" maxWidth="lg" @close="showCreateModal = false">
            <form @submit.prevent="submitCreateUser" class="space-y-4">
                <AppFormField label="Staff Member Name" required :error="userForm.errors.name">
                    <AppInput v-model="userForm.name" placeholder="Sarah Staff" />
                </AppFormField>
                <AppFormField label="Email Address" required :error="userForm.errors.email">
                    <AppInput v-model="userForm.email" placeholder="staff@bubbleautos.com" />
                </AppFormField>
                <AppFormField label="Password" required :error="userForm.errors.password">
                    <AppInput type="password" v-model="userForm.password" placeholder="At least 8 characters..." />
                </AppFormField>
                <AppFormField label="Assigned Role" required :error="userForm.errors.role_id">
                    <AppSelect v-model="userForm.role_id">
                        <option v-for="r in roles" :key="r.id" :value="r.id">
                            {{ r.name }} - {{ r.description }}
                        </option>
                    </AppSelect>
                </AppFormField>
                <div class="flex justify-end gap-3 pt-2">
                    <AppButton variant="outline" @click="showCreateModal = false">Cancel</AppButton>
                    <AppButton type="submit" variant="primary" :loading="userForm.processing">Create Account</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Edit Staff Modal -->
        <AppModal :show="!!editingUser" title="Edit Staff Account" maxWidth="lg" @close="editingUser = null">
            <form @submit.prevent="submitEditUser" class="space-y-4">
                <AppFormField label="Staff Member Name" required :error="editForm.errors.name">
                    <AppInput v-model="editForm.name" />
                </AppFormField>
                <AppFormField label="Email Address" required :error="editForm.errors.email">
                    <AppInput v-model="editForm.email" />
                </AppFormField>
                <AppFormField label="Reset Password (Leave blank to keep existing)">
                    <AppInput type="password" v-model="editForm.password" placeholder="New password..." />
                </AppFormField>
                <AppFormField label="Assigned Role" required :error="editForm.errors.role_id">
                    <AppSelect v-model="editForm.role_id">
                        <option v-for="r in roles" :key="r.id" :value="r.id">
                            {{ r.name }}
                        </option>
                    </AppSelect>
                </AppFormField>
                <div class="flex justify-end gap-3 pt-2">
                    <AppButton variant="outline" @click="editingUser = null">Cancel</AppButton>
                    <AppButton type="submit" variant="primary" :loading="editForm.processing">Save Changes</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Confirm Staff Delete -->
        <ConfirmModal
            :show="!!deletingUser"
            title="Delete Staff Account?"
            message="Are you sure you want to delete this staff account? They will lose access to the system immediately."
            @close="deletingUser = null"
            @confirm="confirmDeleteUser"
        />
</template>
