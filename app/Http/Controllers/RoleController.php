<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with(['permissions', 'users'])->get();
        $permissions = Permission::all()->groupBy('group');

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissionsGrouped' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        ActivityLogService::log(
            'role.created',
            "Created role {$role->name}",
            Role::class,
            $role->id
        );

        return back()->with('success', "Role {$role->name} created successfully.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        ActivityLogService::log(
            'role.updated',
            "Updated permissions for role {$role->name}",
            Role::class,
            $role->id
        );

        return back()->with('success', "Role {$role->name} updated successfully.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $name = $role->name;
        $role->delete();

        ActivityLogService::log(
            'role.deleted',
            "Deleted role {$name}",
            Role::class,
            $role->id
        );

        return back()->with('success', "Role {$name} deleted successfully.");
    }
}
