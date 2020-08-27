<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Captcha\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Captcha\Models\SmsCode;
use App\Modules\Captcha\Support\SmsFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $limit  = $request->input('pageSize', 10);
        $search = $request->input('search', []);

        $page = SmsCode::search($search)->orderByDesc('id')->paginate($limit);

        return $this->success('messages.success', [
            'items'    => $page->items(),
            'total'    => $page->total(),
            'lastPage' => $page->lastPage(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');

        if (SmsCode::destroy($id)) {
            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }

    /**
     * @OA\Post(
     *      path="/api/captcha/v1/sms/send", tags={"公共-验证码"}, summary="发送短信验证码",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="phone", type="string", description="手机号", example="13889898989" ),
     *              @OA\Property( property="key", type="string", description="发送类型", example="default" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="",),
     * )
     */
    public function send(Request $request)
    {
        $validator = Validator::make(
            $request->input(),
            [
                'phone' => 'required|max:11',
            ],
            [
                'phone.required' => '手机号不能空',
                'phone.max'      => '手机号格式不正确',
            ]
        );

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        if ($res = SmsFactory::make()->sendCode($request->input('phone'), null, $request->input('key', 'default'))) {
            return $this->success('短信发送成功', ['code' => $res]);
        }

        return $this->error('发送失败');
    }
}
