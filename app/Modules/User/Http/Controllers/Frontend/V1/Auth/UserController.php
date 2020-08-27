<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Frontend\V1\Auth;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\UserService;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/user/v1/info", tags = {"前端-用户"}, summary="用户基础信息",
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
    public function user()
    {
        $user = UserService::info(auth()->id());

        unset($user['roles']);
        unset($user['permissions']);

        return $this->success('user::messages.get_user_info_success', [
            'user' => $user,
        ]);
    }
}
