<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Backend\V1\Account;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/account/profile", tags = {"后端-用户"}, summary="用户资料修改",
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
    public function profile(Request $request)
    {
        $user = auth()->user();

        $fields = ['nickname', 'avatar'];

        if ($user->fill($request->only($fields))->save()) {
            return $this->success('user::messages.profile_update_success', [
                'user' => UserService::user($user->id)
            ]);
        }

        return $this->error('user::messages.profile_update_error');
    }
}
