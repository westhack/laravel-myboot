<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Captcha\Support;

use App\Modules\Captcha\Models\SmsCode;
use Flc\Dysms\Client;
use Flc\Dysms\Request\SendSms;
use Illuminate\Support\Facades\Cache;

class Aliyun implements AbstractSms
{
    /**
     * 发送短信
     *
     * @param $phone_number
     * @param string $key
     * @param string $code
     *
     * @return bool|string
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendCode($phone_number, $code = null, $key = 'default')
    {
        $config = [
            'accessKeyId'    =>  config('params.captcha.aliyun_sms_access_key_id'),
            'accessKeySecret' => config('params.captcha.aliyun_sms_access_key_secret'),
        ];

        $expire_time = (int)config('params.captcha.sms_expire_time', 5 * 60);
        $code        = $code != '' ? $code : rand(100000, 999999);
        $out_id      = create_order_no('SMS');
        $send_key    = $key .':'. $phone_number;

        try {
//            $client  = new Client($config);
//            $sendSms = new SendSms;
//
//            $sendSms->setPhoneNumbers($phone_number);
//            $sendSms->setSignName(config('_captcha.aliyun_sms_sign_name'));
//            $sendSms->setTemplateCode(config('_captcha.aliyun_sms_template_code'));
//            $sendSms->setTemplateParam(['code' => $code]);
//            $sendSms->setOutId($out_id);
//
//            $res = $client->execute($sendSms);
//            if ($res->Code == 'OK') {

                Cache::store('redis')->set($send_key, $code, $expire_time);
                SmsCode::create([
                    'out_id'       => $out_id,
                    'send_key'     => $send_key,
                    'code'         => $code,
                    'phone_number' => $phone_number,
                    'expire_time'  => time() + $expire_time,
                    'platform'     => 'aliyun',
                   // 'data'         => json_encode($res, JSON_UNESCAPED_UNICODE),
                ]);

                return $code;
//            } else if ($res->Code == 'isv.BUSINESS_LIMIT_CONTROL') {
//
//                return false;
//            }
        } catch (\Throwable $exception) {
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
