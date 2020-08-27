<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Http\Controllers\Backend\V1\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Captcha\Support\SmsFactory;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @OA\Post(
     *      path="/api/backend/user/v1/auth/login", tags = {"后端-用户"}, summary="用户账号密码登录",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="username", type="string", description="账号", example="" ),
     *              @OA\Property( property="password", type="string", description="密码", example="" ),
     *
     *              @OA\Property( property="phone", type="string", description="手机号", example="" ),
     *              @OA\Property( property="sms_code", type="string", description="验证码", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="成功"),
     *      @OA\Response(response="500",description="失败"),
     * )
     */
    public function login(Request $request)
    {
        if ($request->has('phone')) {

            $sms_code = $request->input('sms_code');
            $phone    = $request->input('phone');

            $validator = $this->validatorPhone($request->input());
            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }

            $res = SmsFactory::make()->checkCode($phone, $sms_code, 'login');
            if ($res !== true) {
                return $this->error(trans('captcha::messages.sms_error'));
            }

            $user = User::wherePhone($phone)->first();
            if (! $user) {
                return $this->error('user::messages.phone_unregistered');
            }
        } else {
            $password = $request->input('password');
            $username = $request->input('username');

            $validator = $this->validatorUsername($request->input());
            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }

            $user = User::where('username', $username)->first();
            if (! $user) {
                return $this->error('user::messages.username_unregistered');
            }

            if (Hash::check($password, $user['password']) === false) {
                return $this->error('user::messages.password_error');
            }
        }

        if ($user->is_backend != 1) {
            return $this->error('user::messages.login_error');
        }
        if ($user->status != 1) {
            return $this->error('user::messages.account_not_pass_or_forbidden_login');
        }

        $token = auth('api')->setTTL(86400 * 30)->login($user); // Token 有效期 30 天
        if ($token) {
            $expiration = auth()->getPayload()->get('exp');
            $data       = [
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'expires_in'   => $expiration - time(),
            ];

            return $this->success('user::messages.login_success', $data);
        }

        return $this->error('user::messages.login_error');
    }

    /**
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validatorPhone(array $data)
    {
        return Validator::make(
            $data,
            [
                'phone'    => 'required',
                'sms_code' => 'required',
            ],
            [],
            [
                'phone'    => trans('user::messages.phone'),
                'sms_code' => trans('user::messages.sms_code'),
            ]);
    }

    /**
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validatorUsername(array $data)
    {
        return Validator::make(
            $data,
            [
                'username' => 'required',
                'password' => 'required',
            ],
            [],
            [
                'username' => trans('user::messages.username'),
                'password' => trans('user::messages.password'),
            ]
        );
    }

    /**
     * @return Response
     *
     * @OA\Post(
     *      path="/api/backend/user/v1/auth/refresh/token", tags = {"后端-用户"}, summary="用户刷新 Token",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="成功"),
     *      @OA\Response(response="500",description="失败"),
     * )
     */
    public function refresh()
    {
        $token = $this->respondWithToken($this->guard()->refresh());

        return $this->success(
            'user::messages.refresh_token_success',
            $token
        );
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return array
     */
    protected function respondWithToken($token)
    {
        $user = auth()->setToken($token)->user();
        try {
            if ($user->access_token != '') {
                //auth()->setToken($user->access_token)->logout();
            }
            $user->access_token = $token;
            $user->save();
        } catch (\Throwable $exception) {
        }

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => $this->guard()->factory()->getTTL() * 60,
        ];
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('api');
    }
}
