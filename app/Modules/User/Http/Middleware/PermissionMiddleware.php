<?php

namespace App\Modules\User\Http\Middleware;

use App\Constants\HttpResponseCode;
use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (app('auth')->guest()) {
            return response()->json(['code' => HttpResponseCode::UNAUTHENTICATED, 'message' => trans('user::messages.unauthenticated')], 200);
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (app('auth')->user()->can($permission)) {
                return $next($request);
            }
        }

        return response()->json(['code' => HttpResponseCode::UNAUTHORIZED, 'message' => '未授权'], 200);
    }
}
