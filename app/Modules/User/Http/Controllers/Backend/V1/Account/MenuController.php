<?php

namespace App\Modules\User\Http\Controllers\Backend\V1\Account;

use App\Modules\User\Models\UserMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class MenuController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/backend/user/v1/account/menus", tags = {"后端-用户"}, summary="用户自定义菜单",
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
        $user_id = auth()->id();

        return $this->success('messages.success', [
            'menus' => UserMenu::where('user_id', $user_id)->get()
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/account/menu/create", tags = {"后端-用户"}, summary="用户添加自定义菜单",
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
    public function create(Request $request)
    {
        $user_id = auth()->id();

        if ($request->input('route') == '') {
            $this->error('路由不能为空');
        }

        if ($request->input('name') == '') {
            $this->error('菜单名称不能为空');
        }

        $count = UserMenu::where('user_id', $user_id)->count();
        if ($count > 20) {
            $this->error('只能添加20个自定义菜单');
        }

        $res = UserMenu::create(
            [
                'user_id' => $user_id,
                'route' => $request->input('route'),
                'name' => $request->input('name'),
                'type' => 1,
            ]
        );

        if ($res) {
            return $this->success('messages.success', [
                'menu' => $res
            ]);
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/account/menu/delete", tags = {"后端-用户"}, summary="用户删除自定义菜单",
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
    public function delete(Request $request)
    {
        $id = $request->input('id');
        $user_id = auth()->id();

        $res = UserMenu::where('id', $id)->where('user_id', $user_id)->delete();

        if ($res) {
            return $this->success();
        }

        return $this->error();
    }
}
