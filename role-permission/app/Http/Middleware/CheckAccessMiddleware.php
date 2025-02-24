<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthorizationService;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessMiddleware
{
    protected AuthorizationService $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Lấy tên route và chuyển thành permission tương ứng
        $routeName = $request->route()->getName();
        $permission = $this->authService->convertRouteToPermission($routeName);

        // Kiểm tra quyền
        if (!$this->authService->hasAccess($user, $permission)) {
            abort(403);
        }

        return $next($request);
    }
}
