<?php

namespace App\Modules\User\Http\Middleware;

use App\Constants\HttpResponseCode;
use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            return response()->json(['code' => HttpResponseCode::UNAUTHENTICATED, 'message' => trans('user::messages.unauthenticated')], 200);
        }

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        if (! Auth::user()->hasAnyRole($roles)) {
            return response()->json(['code' => HttpResponseCode::UNAUTHORIZED, 'message' => '未授权'], 200);
        }

        return $next($request);
    }
}
