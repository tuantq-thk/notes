<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthorizationService
{
    public function getUserPermissions($user)
    {
        if (!$user) return [];

        return Cache::remember("user_permissions:{$user->id}", 60, function () use ($user) {
            return $user->getAllPermissions()->pluck('name')->toArray();
        });
    }

    public function getUserRoles($user)
    {
        if (!$user) return [];

        return Cache::remember("user_roles:{$user->id}", 60, function () use ($user) {
            return $user->roles->pluck('name')->toArray();
        });
    }

    public function hasPermission($user, $permission)
    {
        if (!$user) return false;

        return in_array($permission, $this->getUserPermissions($user));
    }

    public function hasRole($user, $role)
    {
        if (!$user) return false;

        return in_array($role, $this->getUserRoles($user));
    }

    public function hasAccess($user, $permission, $role = null)
    {
        if (!$user) return false;

        if ($role) {
            return $this->hasRole($user, $role) && $this->hasPermission($user, $permission);
        }

        return $this->hasPermission($user, $permission);
    }
}
