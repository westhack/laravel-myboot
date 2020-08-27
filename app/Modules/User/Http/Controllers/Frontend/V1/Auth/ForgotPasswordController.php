<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Frontend\V1\Auth;

use App\Modules\Captcha\Support\SmsFactory;
use App\Modules\User\Models\PasswordReset;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * @OA\Post(
     *      path="/api/user/v1/auth/forgot/password", tags = {"前端-用户"}, summary="用户获取重置密码手机验证码",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="phone", type="string", description="手机账号", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function sendResetPhoneCode(Request $request)
    {
        $this->validatePhone($request);

        /** @var User $user **/
        $user = User::where('phone', '=', $request->input('phone'))->first();
        if ($user) {
            $code = SmsFactory::make()->sendCode($request->input('phone'), null, 'ForgotPassword');
            if ($code !== false) {
                $reset_password = PasswordReset::create(
                    [
                        'uuid'       => Str::uuid(),
                        'created_at' => date('Y-m-d H:i:s'),
                        'phone'      => $request->input('phone'),
                        'token'      => $code,
                    ]
                );
                if ($reset_password) {
                    return $this->success('user::messages.reset_password_phone_code_send_success');
                }
            }
        } else {
            return $this->error('user::messages.phone_not_exist');
        }

        return $this->error('user::messages.reset_password_phone_code_send_error');
    }

    protected function validatePhone(Request $request)
    {
        $request->validate(
            [
                'phone' => 'required'
            ],
            [],
            [
                'phone' => trans('user::messages.phone')
            ]
        );
    }

}
