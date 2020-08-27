<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Backend\V1\Auth;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\Permission;
use App\Modules\User\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/auth/user", tags = {"后端-用户"}, summary="用户信息",
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
     * @return Response
     * @throws \Throwable
     */
    public function user()
    {
        $user = UserService::info(auth()->id());

        return $this->success('user::messages.get_user_info_success', [
            'user' => $user,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/auth/menus", tags = {"后端-用户"}, summary="用户菜单权限",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @return Response
     * @throws \Throwable
     */
    public function menus()
    {
        $user = UserService::info(auth()->id());

        return $this->success('messages.success', [
            'menus' => Permission::getTree($user->permissions)
        ]);
    }
}
