<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Captcha\Support;

use Illuminate\Support\Str;

class SmsFactory
{
    /**
     * @param string $name
     *
     * @return AbstractSms
     */
    public static function make($name = null)
    {
        if ($name == null) {
            $name = (config('params.captcha.sms_platform', 'aliyun'));
        }

        $namespace = Str::studly($name);
        $sms = "\\App\\Modules\\Captcha\\Support\\{$namespace}";

        return new $sms();
    }
}
