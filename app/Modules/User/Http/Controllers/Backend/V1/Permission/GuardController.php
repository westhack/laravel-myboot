<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Backend\V1\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GuardController extends Controller
{

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/guard/all", tags = {"后端-权限"}, summary="守卫下拉列表",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @return Response
     */
    public function all()
    {
        $guards = config('auth.guards');
        $items = [];
        foreach ($guards as $key => $val) {
            $items[] = [
                'label' => $key,
                'value' => $key,
            ];
        }
        return $this->success('messages.success',  [
            'items' => $items
        ]);
    }
}
