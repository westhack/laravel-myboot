<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Captcha\Http\Middleware;

use App\Constants\HttpResponseCode;
use App\Modules\Captcha\Support\Img;
use App\Modules\Captcha\Support\SmsFactory;
use Closure;
use Illuminate\Http\Request;

class CheckSmsMiddleware
{
    public function handle(Request $request, Closure $next,  $msg = null, $captchaName = 'captcha', $keyName = 'captcha_key', $phoneName = 'phone')
    {
        if ($msg == '') {
            $msg = 'captcha::messages.sms_error';
        }

        $captcha = $request->input($captchaName);
        $key     = $request->input($keyName);
        $phone   = $request->input($phoneName);

        if ($captcha == '' || $key == '') {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('captcha::messages.sms') . trans('captcha::messages.captcha_not_null')], 200);
        }

        if ($phone == '') {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('captcha::messages.phone_not_null')], 200);
        }

        $res = SmsFactory::make()->checkCode($phone, $captcha, $key);
        if ($res !== true) {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans($msg)], 200);
        }


        return $next($request);
    }
}
