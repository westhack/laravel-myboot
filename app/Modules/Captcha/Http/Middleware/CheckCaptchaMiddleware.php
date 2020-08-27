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

class CheckCaptchaMiddleware
{
    public function handle(Request $request, Closure $next, $type, $captchaName = null, $keyName = null, $msg = null, $phoneName = null)
    {
        if ($msg == '') {
            if ($type == 'sms') {
                $msg = 'captcha::messages.sms_error';
            } else {
                $msg = 'captcha::messages.img_error';
            }
        }

        if ($captchaName == '') {
            $captchaName = 'captcha';
        }

        if ($keyName == '') {
            $keyName = 'captcha_key';
        }

        $captcha = request($captchaName);
        $key     = request($keyName);

        if ($captcha == '' || $key == '') {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('captcha::messages.' . $type) . trans('captcha::messages.captcha_not_null')], 200);
        }

        if ($type == 'sms') {
            if ($phoneName == '') {
                $phoneName = 'phone';
            }

            $phone = $request->input($phoneName);

            if ($phone == '') {
                return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('captcha::messages.phone_not_null')], 200);
            }

            $res = SmsFactory::make()->checkCode($phone, $captcha, $key);
            if ($res !== true) {
                return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans($msg)], 200);
            }
        } else {

            if (!Img::check($captcha, $key)) {
                return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans($msg)], 200);
            }
        }

        return $next($request);
    }
}
