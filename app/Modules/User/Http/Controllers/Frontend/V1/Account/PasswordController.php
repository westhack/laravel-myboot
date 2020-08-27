<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Frontend\V1\Account;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/user/v1/account/reset/password", tags = {"前端-用户"}, summary="用户密码修改",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="old_password", type="string", description="原密码", example="" ),
     *              @OA\Property( property="new_password", type="string", description="新密码", example="" ),
     *              @OA\Property( property="new_password_confirmation", type="string", description="确认密码", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="正常操作响应")
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request)
    {
        $this->validatePassword($request);

        $user = $request->user();

        $password = User::whereId($user->id)->value('password');
        if (Hash::check($request->input('old_password'), $password) === false) {
            return $this->error(trans('user::messages.old_password_error'));
        }

        $res = $request->user()->update([
            'password' => bcrypt($request->input('new_password')),
        ]);

        if ($res) {
            return $this->success('user::messages.update_password_success');
        }

        return $this->error('user::messages.update_password_error');
    }

    /**
     * @param Request $request
     */
    protected function validatePassword(Request $request)
    {
        $request->validate(
            [
                'old_password' => 'required',
                'new_password' => 'required|confirmed|min:6',
            ],
            [
                'new_password.confirmed' => trans('user::messages.re_set_password_not_match'),
            ],
            [
                'old_password' => trans('user::messages.old_password'),
                'new_password' => trans('user::messages.new_password'),
            ]
        );
    }
}
