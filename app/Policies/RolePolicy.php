<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function update(User $user, ?Role $role = null): bool
    {
        return $user->hasPermission('roles.manage');
    }

    public function delete(User $user, ?Role $role = null): bool
    {
        return $user->hasPermission('roles.manage') && ($role === null || ! in_array($role->slug, ['admin', 'staff', 'manager']));
    }
}
