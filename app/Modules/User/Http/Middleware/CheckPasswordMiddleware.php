<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Middleware;

use App\Constants\HttpResponseCode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CheckPasswordMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app('auth')->guest()) {
            return response()->json(['code' => HttpResponseCode::UNAUTHENTICATED, 'message' => trans('user::messages.unauthenticated')], 200);
        }

        $user = app('auth')->user();
        $password = $user->password;

        if (empty($request->input('password'))) {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('user::messages.password_not_null')], 200);
        }

        if (Hash::check($request->input('password'), $password) === false) {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('user::messages.password_error')], 200);
        }

        return $next($request);
    }
}
