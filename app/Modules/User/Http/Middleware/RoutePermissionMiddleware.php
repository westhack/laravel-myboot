<?php

namespace App\Modules\User\Http\Middleware;

use App\Constants\HttpResponseCode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoutePermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app('auth')->guest()) {
            return response()->json(['code' => HttpResponseCode::UNAUTHENTICATED, 'message' => trans('user::messages.unauthenticated')], 200);
        }

        $user = app('auth')->user();

        if ($user->username == 'admin') {
            return $next($request);
        }

        $permissions = $user->getPermissions();
        $route       = Route::current()->uri();

        $exist = $permissions->search(static function ($item, $key) use ($route) {
            return in_array($route, $item['route']);
        });

        if ($exist === false) {
            return response()->json(['code' => HttpResponseCode::UNAUTHORIZED, 'message' => trans('user::messages.unauthorized')], 200);
        }

        return $next($request);
    }
}
