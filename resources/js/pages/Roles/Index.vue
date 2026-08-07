<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppPageHeader from '@/components/common/AppPageHeader.vue';
import AppCard from '@/components/common/AppCard.vue';
import AppButton from '@/components/common/AppButton.vue';
import AppModal from '@/components/common/AppModal.vue';
import ConfirmModal from '@/components/common/ConfirmModal.vue';
import AppFormField from '@/components/common/AppFormField.vue';
import AppInput from '@/components/common/AppInput.vue';
import AppTextarea from '@/components/common/AppTextarea.vue';
import { showToast } from '@/components/common/AppToast.vue';
import { Plus, Shield, Check, Trash2, Edit } from '@lucide/vue';

interface Props {
    roles: any[];
    permissionsGrouped: Record<string, any[]>;
}

const props = defineProps<Props>();

// Create Role Form
const showCreateModal = ref(false);
const roleForm = useForm({
    name: '',
    description: '',
    permissions: [] as number[],
});

const submitCreateRole = () => {
    roleForm.post('/roles', {
        onSuccess: () => {
            showCreateModal.value = false;
            roleForm.reset();
            showToast.success('Role created successfully.');
        },
    });
};

// Edit Role Form
const editingRole = ref<any>(null);
const editRoleForm = useForm({
    name: '',
    description: '',
    permissions: [] as number[],
});

const openEditRole = (r: any) => {
    editingRole.value = r;
    editRoleForm.name = r.name;
    editRoleForm.description = r.description || '';
    editRoleForm.permissions = r.permissions ? r.permissions.map((p: any) => p.id) : [];
};

const submitEditRole = () => {
    if (!editingRole.value) return;
    editRoleForm.patch(`/roles/${editingRole.value.id}`, {
        onSuccess: () => {
            editingRole.value = null;
            showToast.success('Role permissions updated successfully.');
        },
    });
};

// Delete Role Confirm Modal
const deletingRole = ref<any>(null);
const confirmDeleteRole = () => {
    if (!deletingRole.value) return;
    router.delete(`/roles/${deletingRole.value.id}`, {
        onSuccess: () => {
            deletingRole.value = null;
            showToast.success('Role deleted.');
        },
    });
};
</script>

<template>
    <Head title="Roles & Permissions - BAMS" />

    <div class="space-y-6">
            <AppPageHeader title="Roles & Permission Matrix" description="Configure user roles and assign granular module permissions">
                <template #actions>
                    <AppButton variant="primary" @click="showCreateModal = true">
                        <Plus class="w-4 h-4" /> Create Custom Role
                    </AppButton>
                </template>
            </AppPageHeader>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <AppCard v-for="r in roles" :key="r.id" :title="r.name" :description="r.description || 'System access role'">
                    <template #headerActions>
                        <div class="flex items-center gap-1">
                            <button @click="openEditRole(r)" class="p-1 rounded text-slate-400 hover:text-blue-600" title="Edit Permissions">
                                <Edit class="w-4 h-4" />
                            </button>
                            <button
                                v-if="!['admin', 'staff', 'manager'].includes(r.slug)"
                                @click="deletingRole = r"
                                class="p-1 rounded text-slate-400 hover:text-red-600"
                                title="Delete Role"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-500 font-semibold border-b pb-2 dark:border-slate-700">
                            <span>Assigned Users</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ r.users?.length || 0 }} user(s)</span>
                        </div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Module Permissions</div>
                        <div class="flex flex-wrap gap-1.5 max-h-48 overflow-y-auto pr-1">
                            <span
                                v-for="p in r.permissions"
                                :key="p.id"
                                class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                            >
                                {{ p.name }}
                            </span>
                        </div>
                    </div>
                </AppCard>
            </div>
        </div>

        <!-- Create Role Modal -->
        <AppModal :show="showCreateModal" title="Create Custom Role" maxWidth="xl" @close="showCreateModal = false">
            <form @submit.prevent="submitCreateRole" class="space-y-4">
                <AppFormField label="Role Name" required :error="roleForm.errors.name">
                    <AppInput v-model="roleForm.name" placeholder="e.g. Finance Officer" />
                </AppFormField>

                <AppFormField label="Role Description">
                    <AppTextarea v-model="roleForm.description" placeholder="Description of role access scope..." :rows="2" />
                </AppFormField>

                <div class="space-y-3 pt-2 border-t dark:border-slate-700">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Module Permissions</h4>
                    <div v-for="(perms, group) in permissionsGrouped" :key="group" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 space-y-2">
                        <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">{{ group }}</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <label v-for="p in perms" :key="p.id" class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                <input type="checkbox" :value="p.id" v-model="roleForm.permissions" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span>{{ p.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <AppButton variant="outline" @click="showCreateModal = false">Cancel</AppButton>
                    <AppButton type="submit" variant="primary" :loading="roleForm.processing">Create Role</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Edit Role Modal -->
        <AppModal :show="!!editingRole" :title="`Edit Permissions: ${editingRole?.name}`" maxWidth="xl" @close="editingRole = null">
            <form @submit.prevent="submitEditRole" class="space-y-4">
                <AppFormField label="Role Name" required :error="editRoleForm.errors.name">
                    <AppInput v-model="editRoleForm.name" />
                </AppFormField>

                <AppFormField label="Role Description">
                    <AppTextarea v-model="editRoleForm.description" :rows="2" />
                </AppFormField>

                <div class="space-y-3 pt-2 border-t dark:border-slate-700">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Module Permissions</h4>
                    <div v-for="(perms, group) in permissionsGrouped" :key="group" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 space-y-2">
                        <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">{{ group }}</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <label v-for="p in perms" :key="p.id" class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                                <input type="checkbox" :value="p.id" v-model="editRoleForm.permissions" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                <span>{{ p.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <AppButton variant="outline" @click="editingRole = null">Cancel</AppButton>
                    <AppButton type="submit" variant="primary" :loading="editRoleForm.processing">Save Permissions</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Confirm Delete Role -->
        <ConfirmModal
            :show="!!deletingRole"
            title="Delete Custom Role?"
            message="Are you sure you want to delete this custom role? Users assigned to this role should be reassigned beforehand."
            @close="deletingRole = null"
            @confirm="confirmDeleteRole"
        />
</template>
