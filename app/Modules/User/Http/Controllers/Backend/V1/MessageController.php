<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Http\Controllers\Backend\V1;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\UserMessage;
use App\Modules\User\Models\Filters\UserMessageFilter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/backend/user/v1/user/messages", tags = {"后端-用户"}, summary="用户消息列表",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="search", type="string", description="搜索字段", example="" ),
     *              @OA\Property( property="sort_order", type="string", description="排序规则", example="" ),
     *              @OA\Property( property="limit", type="string", description="分页大小", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function index(Request $request)
    {
        $input = $request->input();

        $items = UserMessage::where('is_backend', 1)->filter($input, UserMessageFilter::class)->orderByDesc('id')->limit(10)->get();

        return $this->success('messages.success', [
            'items' => $items,
            'total' => UserMessage::where('is_backend', 1)->filter($input, UserMessageFilter::class)->where('status', 0)->count(),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/message/view", tags = {"后端-用户"}, summary="消息标记已读",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="id", type="string", description="id", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function view(Request $request)
    {
        $id = $request->input('id');

        if ($id && UserMessage::whereIn('id', array_wrap($id))->where('user_id', auth()->user()->id)->update([
            'status'    => UserMessage::STATUS_YES,
            'view_time' => date('Y-m-d H:i:s'),
        ])) {
            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/message/delete", tags = {"后端-用户"}, summary="消息删除",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="id", type="string", description="id", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');

        if ($id && UserMessage::whereIn('id', array_wrap($id))->where('user_id', auth()->user()->id)->delete()) {
            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }
}
