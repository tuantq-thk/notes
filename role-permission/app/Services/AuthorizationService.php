<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthorizationService
{
    /**
     * Kiểm tra user có permission trực tiếp hay không.
     */
    public function hasPermission(Authenticatable $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    /**
     * Kiểm tra user có Role nào chứa permission này không.
     */
    public function hasRoleWithPermission(Authenticatable $user, string $permission): bool
    {
        return $user->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    /**
     * Kiểm tra user có quyền thông qua Role hoặc trực tiếp.
     */
    public function hasAccess(Authenticatable $user, string $permission): bool
    {
        return $this->hasPermission($user, $permission) || $this->hasRoleWithPermission($user, $permission);
    }

    /**
     * Chuyển đổi route name thành permission name (ví dụ: posts.index -> posts_index).
     */
    public function convertRouteToPermission(string $routeName): string
    {
        [$resource, $action] = explode('.', $routeName, 2);
        $permission = Str::singular($resource) . '_' . $action;

        return $permission;
    }
}
