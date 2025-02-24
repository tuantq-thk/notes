<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthorizationService;
use Illuminate\Support\Str;

class CheckAccessMiddleware
{
    protected $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Lấy route name và chuyển thành permission
        $routeName = $request->route()->getName(); // Ví dụ: posts.index
        $permission = Str::replace('.', '_', $routeName); // posts.index -> posts_index


        // Kiểm tra quyền với Role (nếu có)
        if (!$this->authService->hasAccess($user, $permission, $role)) {
            abort(403, "Bạn không có quyền thực hiện hành động này.");
        }

        return $next($request);
    }
}
