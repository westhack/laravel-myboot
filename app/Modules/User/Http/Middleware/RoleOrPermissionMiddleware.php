<?php

namespace App\Modules\User\Http\Middleware;

use App\Constants\HttpResponseCode;
use Closure;
use Illuminate\Support\Facades\Auth;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission)
    {
        if (Auth::guest()) {
            return response()->json(['code' => HttpResponseCode::UNAUTHENTICATED, 'message' => trans('user::messages.unauthenticated')], 200);
        }

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        if (! Auth::user()->hasAnyRole($rolesOrPermissions) && ! Auth::user()->hasAnyPermission($rolesOrPermissions)) {
            return response()->json(['code' => HttpResponseCode::UNAUTHORIZED, 'message' => trans('user::messages.unauthorized')], 200);
        }

        return $next($request);
    }
}
