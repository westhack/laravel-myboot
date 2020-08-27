<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace  App\Modules\Captcha\Support;

interface AbstractSms
{
    public function sendCode($phone_number, $code = null, $key = 'default');

    public function checkCode($phone_number, $code, $key = 'default');
}
