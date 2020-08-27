<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Captcha\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Captcha\Support\Img;

class ImgController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/captcha/v1/img/api", tags={"公共-验证码"}, summary="获取图形验证码",
     *      @OA\Response(
     *          response="200",
     *          description="",
     *      ),
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function api()
    {
        $res = Img::api();
        if ($res) {

            return $this->success('messages.success', $res);
        }

        return $this->error('messages.error');
    }

}
