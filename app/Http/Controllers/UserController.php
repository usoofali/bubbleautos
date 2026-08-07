<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $search = trim($request->input('search', ''));

        $query = User::with(['role', 'directPermissions']);

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $users = $query->orderBy('name')->paginate(12)->withQueryString();
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('group');

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissionsGrouped' => $permissions,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'direct_permissions' => 'nullable|array',
            'direct_permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (! empty($validated['direct_permissions'])) {
            $user->directPermissions()->sync($validated['direct_permissions']);
        }

        ActivityLogService::log(
            'user.created',
            "Created staff account for {$user->name} ({$user->email})",
            User::class,
            $user->id
        );

        return back()->with('success', "Staff account for {$user->name} created successfully.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8',
            'direct_permissions' => 'nullable|array',
            'direct_permissions.*' => 'exists:permissions,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if (isset($validated['direct_permissions'])) {
            $user->directPermissions()->sync($validated['direct_permissions']);
        }

        ActivityLogService::log(
            'user.updated',
            "Updated staff account for {$user->name}",
            User::class,
            $user->id
        );

        return back()->with('success', "Staff account for {$user->name} updated successfully.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => ! $user->is_active]);

        $statusText = $user->is_active ? 'activated' : 'deactivated';

        ActivityLogService::log(
            'user.toggle_status',
            "Staff account for {$user->name} {$statusText}",
            User::class,
            $user->id
        );

        return back()->with('success', "Staff account for {$user->name} has been {$statusText}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $name = $user->name;
        $user->delete();

        ActivityLogService::log(
            'user.deleted',
            "Deleted staff account for {$name}",
            User::class,
            $user->id
        );

        return back()->with('success', "Staff account for {$name} deleted successfully.");
    }
}
