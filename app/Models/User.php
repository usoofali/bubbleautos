<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin' || $this->role?->slug === 'administrator';
    }

    public function isManager(): bool
    {
        return $this->role?->slug === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role?->slug === 'staff';
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Direct user permission override check
        if ($this->directPermissions->contains('slug', $permissionSlug)) {
            return true;
        }

        // Role permission check
        return $this->role ? $this->role->permissions->contains('slug', $permissionSlug) : false;
    }

    public function permissionSlugs(): array
    {
        if ($this->isAdmin()) {
            return Permission::pluck('slug')->toArray();
        }

        $rolePerms = $this->role ? $this->role->permissions->pluck('slug') : collect();
        $directPerms = $this->directPermissions->pluck('slug');

        return $rolePerms->merge($directPerms)->unique()->values()->toArray();
    }
}
