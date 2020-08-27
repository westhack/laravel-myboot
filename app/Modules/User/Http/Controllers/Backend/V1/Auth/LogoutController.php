<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Http\Controllers\Backend\V1\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Shop\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/auth/logout", tags = {"前端-用户"}, summary="用户退出",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=false,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="正常操作响应")
     * )
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        return $this->success('user::messages.logout_success');
    }
}
