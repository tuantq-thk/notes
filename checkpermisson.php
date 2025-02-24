<?php

// Hybrid RBAC & PBAC

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Lấy action từ route hiện tại
        $routeName = $request->route()->getName(); // Ví dụ: "posts.index"
        $permission = str_replace('.', '_', $routeName); // Ví dụ: "posts_index"

        // Lấy role từ metadata của route (nếu có)
        $requiredRole = $request->route()->defaults['role'] ?? null; // Ví dụ: 'admin'

        // Kiểm tra Role nếu có yêu cầu
        if ($requiredRole && !$user->hasRole($requiredRole)) {
            abort(403, "Bạn cần quyền $requiredRole để truy cập.");
        }

        // Kiểm tra Permission
        if (!$user->hasPermissionTo($permission)) {
            abort(403, "Bạn không có quyền thực hiện hành động này.");
        }

        return $next($request);
    }
}


// Route::middleware('check_permission')->group(function () {
//     Route::get('/posts', 'PostController@index')->name('posts.index')->defaults('role', 'editor');
//     Route::post('/posts', 'PostController@store')->name('posts.store')->defaults('role', 'admin');
//     Route::put('/posts/{id}', 'PostController@update')->name('posts.update')->defaults('role', 'editor');
//     Route::delete('/posts/{id}', 'PostController@destroy')->name('posts.destroy')->defaults('role', 'admin');
// });

use Closure;
use Illuminate\Http\Request;

class CheckRolePermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Kiểm tra nếu user có Role chứa permission
        if ($user->roles->contains(fn ($role) => $role->hasPermissionTo($permission))) {
            return $next($request);
        }

        // Kiểm tra nếu user có Permission trực tiếp
        if ($user->hasPermissionTo($permission)) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập.');
    }
}


// cách 2
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthorizationService
{
    protected const CACHE_TIME = 60; // Cache 60 phút

    // Lấy tất cả quyền của User từ cache hoặc DB
    public function getUserPermissions($user)
    {
        return Cache::remember("user_permissions_{$user->id}", self::CACHE_TIME, function () use ($user) {
            return [
                'roles' => $user->roles->pluck('name')->toArray(),
                'direct_permissions' => $user->permissions->pluck('name')->toArray(),
                'revoked_permissions' => $user->revokedPermissions()->pluck('name')->toArray(), // Lưu quyền bị thu hồi
                'role_permissions' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ];
        });
    }

    // Kiểm tra quyền (PBAC + RBAC + Thu hồi quyền)
    public function hasPermission($user, $permission)
    {
        $permissions = $this->getUserPermissions($user);

        // Nếu User có quyền trực tiếp → Được phép
        if (in_array($permission, $permissions['direct_permissions'])) {
            return true;
        }

        // Nếu User có Role và Role có quyền, nhưng quyền bị thu hồi → Không được phép
        if (in_array($permission, $permissions['role_permissions']) &&
            !in_array($permission, $permissions['revoked_permissions'])) {
            return true;
        }

        return false; // Không có quyền
    }

    // Kiểm tra Role
    public function hasRole($user, $role)
    {
        $permissions = $this->getUserPermissions($user);
        return in_array($role, $permissions['roles']);
    }
}
