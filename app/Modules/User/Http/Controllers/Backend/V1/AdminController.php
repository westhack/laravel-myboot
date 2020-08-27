<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Backend\V1;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\Form\Admin\AdminForm;
use App\Modules\User\Services\Form\Admin\AdminFormRequest;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/admin/list", tags = {"后端-用户"}, summary="管理员",
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
        $search    = $request->input('search');
        $sortOrder = $request->input('sortOrder');
        $limit     = $request->input('pageSize', 10);

        $page = User::where('is_backend', 1)->search($search)->with('roles')->sortOrder($sortOrder)->paginate($limit);

        return $this->success('messages.success', [
            'items'     => $page->items(),
            'total'     => $page->total(),
            'lastPage' => $page->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/admin/create", tags = {"后端-用户"}, summary="管理员添加",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="username", type="string", description="账号", example="" ),
     *              @OA\Property( property="password", type="string", description="密码", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function create(AdminFormRequest $request, AdminForm $form)
    {
        $input = $request->onlyInput();
        $input['is_backend'] = 1;
        if ($model = $form->save($request->onlyInput())) {
            return $this->success(
                'messages.success',
                [
                    'user' => $model
                ]
            );
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/admin/update", tags = {"后端-用户"}, summary="管理员更新",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="id", type="string", description="用户ID", example="" ),
     *              @OA\Property( property="username", type="string", description="账号", example="" ),
     *              @OA\Property( property="password", type="string", description="密码", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function update(AdminFormRequest $request, AdminForm $form)
    {
        if ($model = $form->update($request->onlyInput())) {
            return $this->success(
                'messages.success',
                [
                    'user' => $model
                ]
            );
        }

        return $this->error('messages.error');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/admin/batch/update", tags = {"后端-用户"}, summary="管理员批量修改",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="id", type="string", description="用户ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function batchUpdate(Request $request)
    {
        $data = $request->input();

        if (User::batchUpdate($data, 'id')) {
            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/admin/delete", tags = {"后端-用户"}, summary="管理员删除",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="id", type="string", description="用户ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');

        if (in_array(1, array_wrap($id))) {
            return $this->error('user::messages.can_not_delete_super_admin');
        }

        if (User::whereIn('id', array_wrap($id))->delete()) {
            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }
}
