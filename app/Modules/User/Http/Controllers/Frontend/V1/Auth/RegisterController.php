<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Frontend\V1\Auth;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/user/v1/auth/register", tags = {"前端-用户"}, summary="用户账号注册",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="phone", type="string", description="手机号", example="" ),
     *              @OA\Property( property="email", type="string", description="邮箱", example="" ),
     *              @OA\Property( property="username", type="string", description="账号", example="" ),
     *              @OA\Property( property="password", type="string", description="密码", example="" ),
     *              @OA\Property( property="password_confirmation", type="string", description="确认密码", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->input());
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        if ($this->create($request->input())) {
            return $this->success('user::messages.register_success');
        }

        return $this->error('user::messages.register_error');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'phone'    => 'required|max:255|unique:users',
                'email'    => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
            ],
            [],
            [
                'phone'    => trans('user::messages.phone'),
                'email'    => trans('user::messages.email'),
                'password' => trans('user::messages.password'),
            ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $referrer = User::where('id', data_get($data, 'referrer'))->first();

        return User::create(
            [
                'phone'            => $data['phone'],
                'email'            => $data['email'],
                'username'         => array_get($data, 'username', $data['phone']),
                'password'         => bcrypt($data['password']),
                'status'           => 1,
                'referrer_user_id' => data_get($referrer, 'id'),
            ]
        );
    }
}
