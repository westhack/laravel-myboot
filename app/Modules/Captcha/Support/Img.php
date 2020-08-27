<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Captcha\Support;

use Illuminate\Support\Facades\Cache;

class Img
{
    /**
     * 发送 API 图形验证码
     *
     * @return mixed
     */
    public static function api()
    {
        try {
            $res = app('captcha')->create('default', true);

            Cache::store('redis')->set($res['key'], 1, 86400);

            return $res;
        } catch (\Psr\SimpleCache\InvalidArgumentException $exception) {
            logger('CAPTCHA:' . $exception->getMessage());
        }

        return false;
    }

    /**
     * 接口图形验证码验证
     *
     * @param $captcha
     * @param $key
     * @return bool
     */
    public static function check($captcha, $key)
    {
        if (!Cache::store('redis')->get($key)) {

            return false;
        }

        $res = captcha_api_check($captcha, $key);

        return $res;
    }
}
