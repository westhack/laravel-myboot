<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Captcha\Support;

use App\Modules\Captcha\Models\SmsCode;
use Illuminate\Support\Facades\Cache;
use Qcloud\Sms\SmsSingleSender;

class Qcloud implements AbstractSms
{
    public function sendCode($phone_number, $code = null, $key = 'default')
    {
        $expire_time = (int)config('params.captcha.sms_expire_time', 5 * 60);
        $code        = $code != '' ? $code : rand(100000, 999999);
        $out_id      = create_order_no('SMS');
        $send_key    = $key .':'. $phone_number;

        try {
            $ssender = new SmsSingleSender(config('params.captcha.qcloud_sms_app_id'), config('params.captcha.qcloud_sms_app_key'));

            $params = [$code];
            $result = $ssender->sendWithParam(
                "86",
                $phone_number,
                config('params.captcha.qcloud_sms_template_id'),
                $params,
                config('params.captcha.qcloud_sms_sign'),
                "",
                ""
            );

            $rsp = json_decode($result, true);
            if ($rsp['result'] == 0) {

                Cache::store('redis')->set($send_key, $code, $expire_time);
                SmsCode::create([
                    'out_id'       => $out_id,
                    'send_key'     => $send_key,
                    'code'         => $code,
                    'phone_number' => $phone_number,
                    'expire_time'  => time() + $expire_time,
                    'platform'     => 'qcloud',
                    'data'         => json_encode($rsp, JSON_UNESCAPED_UNICODE),
                ]);

                return true;
            }
        } catch(\Throwable $exception) {
            logger($exception->getMessage());
        }

        return false;
    }

    /**
     * 验证短信验证码
     *
     * @param string $phone_number
     * @param string $key
     * @param string $code
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function checkCode($phone_number, $code, $key = 'default')
    {
        $is_super_code = config('module.captcha.is_super_code', false);
        $super_code = config('module.captcha.super_code');
        $send_key = $key .':'. $phone_number;
        if (Cache::store('redis')->get($send_key) == $code || ($is_super_code && $super_code == $code)) {
            Cache::store('redis')->delete($send_key);

            return true;
        }

        return false;
    }
}
