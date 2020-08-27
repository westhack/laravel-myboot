<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Backend\V1\Permission;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\Permission;
use App\Modules\User\Models\Role;
use App\Modules\User\Services\Form\Role\RoleForm;
use App\Modules\User\Services\Form\Role\RoleFormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/list", tags = {"后端-角色"}, summary="角色列表",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @return Response
     */
    public function index()
    {
        return $this->success(
            'messages.success',
            [
                'items' => Role::with('permissions')->get(),
            ]
        );
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/create", tags = {"后端-角色"}, summary="角色添加",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param RoleFormRequest $request
     * @param RoleForm $form
     * @return \Illuminate\Http\Response
     */
    public function create(RoleFormRequest $request, RoleForm $form)
    {
        if ($res = $form->save($request->onlyInput())) {
            return $this->success();
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/update", tags = {"后端-角色"}, summary="角色更新",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param RoleFormRequest $request
     * @param RoleForm $form
     * @return \Illuminate\Http\Response
     */
    public function update(RoleFormRequest $request, RoleForm $form)
    {
        if ($id = $request->input('id')) {
            if ($res = $form->update($request->onlyInput())) {
                return $this->success();
            }
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/delete", tags = {"后端-角色"}, summary="角色删除",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if ($id = $request->input('id')) {
            if ($id === 1) {
                return $this->error();
            }

            if ($res = Role::destroy($id)) {
                return $this->success();
            }
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/all", tags = {"后端-角色"}, summary="角色下拉列表",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        $roles = Role::select('title as label', 'id as value', 'name')->get();

        return $this->success('messages.success',  [
            'items' => $roles
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/sync/permissions", tags = {"后端-角色"}, summary="角色权限同步",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="role_id", type="string", description="角色ID", example="" ),
     *              @OA\Property( property="permission_id", type="array", description="权限IDs", @OA\Items() ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function syncPermissions(Request $request)
    {
        $role = Role::where('id', '=', $request->input('role_id'))->firstOrFail();

        $permissions = Permission::whereIn('id', array_wrap($request->input('permission_id')))->get();

        $role->syncPermissions($permissions);

        return $this->success('messages.success');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/give/permission", tags = {"后端-角色"}, summary="角色赋权",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="role_id", type="string", description="角色ID", example="" ),
     *              @OA\Property( property="permission_id", type="string", description="权限ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function givePermission(Request $request)
    {
        $role = Role::where('id', '=', $request->input('role_id'))->firstOrFail();

        $permissions = Permission::whereIn('id', array_wrap($request->input('permission_id')))->get();

        if ($permissions) {
            foreach ($permissions as $permission) {
                $role->givePermissionTo($permission);
            }
        }

        return $this->success('messages.success');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/revoke/permission", tags = {"后端-角色"}, summary="角色撤回权限",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="role_id", type="string", description="角色ID", example="" ),
     *              @OA\Property( property="permission_id", type="string", description="权限ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function revokePermission(Request $request)
    {
        $role = Role::where('id', '=', $request->input('role_id'))->firstOrFail();

        $permissions = Permission::whereIn('id', array_wrap($request->input('permission_id')))->get();

        if ($permissions) {
            foreach ($permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
        }

        return $this->success('messages.success');
    }
}
