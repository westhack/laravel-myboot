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

class CheckImgMiddleware
{
    public function handle(Request $request, Closure $next,  $msg = null, $captchaName = 'captcha', $keyName = 'captcha_key')
    {
        if ($msg == '') {
            $msg = 'captcha::messages.img_error';
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
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans('captcha::messages.img') . trans('captcha::messages.captcha_not_null')], 200);
        }

        if (!Img::check($captcha, $key)) {
            return response()->json(['code' => HttpResponseCode::ERROR, 'message' => trans($msg)], 200);
        }


        return $next($request);
    }
}
