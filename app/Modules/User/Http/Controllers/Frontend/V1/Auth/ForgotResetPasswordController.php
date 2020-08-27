<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Frontend\V1\Auth;

use App\Modules\User\Models\PasswordReset;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotResetPasswordController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'resetWithPhone'
            ]
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/user/v1/auth/forgot/reset/password", tags = {"前端-用户"}, summary="用户手机账号密码重置",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="phone", type="string", description="手机号", example="" ),
     *              @OA\Property( property="code", type="string", description="验证码", example="" ),
     *              @OA\Property( property="password", type="string", description="新密码", example="" ),
     *              @OA\Property( property="password_confirmation", type="string", description="确认密码", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function phone(Request $request)
    {
        $this->validatePhone($request);

        $code = PasswordReset::where('phone', '=', $request->input('phone'))->orderByDesc('created_at')->first();
        if ($code['token'] != $request->input('code')) {
            return $this->error('user::messages.reset_password_error');
        }

        if ($code && (strtotime($code['created_at']) + 15 * 60) <= time()) {

            if ($this->resetPassword($code, $request->input('password'))) {
                return $this->success('user::messages.reset_password_success');
            }
        }

        return $this->error('user::messages.reset_password_error');
    }

    /**
     * @param Request $request
     */
    protected function validatePhone(Request $request)
    {
        $request->validate(
            [
                'phone'    => 'required',
                'code'     => 'required',
                'password' => 'required|min:6',
            ],
            [],
            [
                'phone'    => trans('user::messages.phone'),
                'code'     => trans('user::messages.sms_code'),
                'password' => trans('user::messages.password'),
            ]
        );
    }

    /**
     * @param PasswordReset $code
     * @param $password
     *
     * @return bool|mixed
     *
     * @throws \Throwable
     */
    protected function resetPassword(PasswordReset $code, $password)
    {
        return DB::transaction(function () use($code, $password) {
            /** @var User $user **/
            $user = User::where('phone', '=', $code->phone)->first();
            if ($user) {
                return $user->fill(['password' => bcrypt($password)])->save() && $code->delete();
            }

            return false;
        });
    }
}
