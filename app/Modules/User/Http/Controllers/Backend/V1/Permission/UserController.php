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
use App\Modules\User\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/user/give/permission", tags = {"后端-用户"}, summary="用户赋权",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="user_id", type="string", description="用户ID", example="" ),
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
        $user = User::where('id', '=', $request->input('user_id'))->firstOrFail();

        $permissions = Permission::whereIn('id', array_wrap($request->input('permission_id')))->get();

        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission);
        }

        return $this->success('messages.success');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/user/revoke/permission", tags = {"后端-用户"}, summary="用户撤销权限",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="user_id", type="string", description="用户ID", example="" ),
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
        $user = User::where('id', '=', $request->input('user_id'))->firstOrFail();

        $permissions = Permission::whereIn('id', array_wrap($request->input('permission_id')))->get();

        if ($permissions) {
            foreach ($permissions as $permission) {
                $user->revokePermissionTo($permission);
            }
        }

        return $this->success('messages.success');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/user/sync/permissions", tags = {"后端-用户"}, summary="用户权限同步",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="role_id", type="string", description="用户ID", example="" ),
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
        $role = User::where('id', '=', $request->input('user_id'))->firstOrFail();

        $permissions = Permission::whereIn('id', array_wrap($request->input('permission_id')))->get();
        
        $role->syncPermissions($permissions);

        return $this->success('messages.success');
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/user/assign/role", tags = {"后端-用户"}, summary="用户关联角色",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="user_id", type="string", description="用户ID", example="" ),
     *              @OA\Property( property="role_id", type="string", description="角色ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function assignRole(Request $request)
    {
        $roles = Role::whereIn('id', array_wrap($request->input('role_id')))->pluck('name');
        if ($roles) {
            $user = User::where('id', '=', $request->input('user_id'))->firstOrFail();

            if ($user->assignRole($roles)) {
                return $this->success('messages.success');
            }
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/user/remove/role", tags = {"后端-用户"}, summary="用户删除角色",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="user_id", type="string", description="用户ID", example="" ),
     *              @OA\Property( property="role_id", type="string", description="角色ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeRole($request)
    {
        $roles = Role::whereIn('id', array_wrap($request->input('role_id')))->pluck('name');
        if ($roles) {
            $user = User::where('id', '=', $request->input('user_id'))->firstOrFail();

            if ($user->removeRole($roles)) {
                return $this->success('messages.success');
            }
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/role/sync/roles", tags = {"后端-用户"}, summary="用户同步角色",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="user_id", type="string", description="用户ID", example="" ),
     *              @OA\Property( property="role_id", type="array", description="角色IDs", @OA\Items() ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function syncRoles(Request $request)
    {
        $roles = Role::whereIn('id', array_wrap($request->input('role_id')))->pluck('name');
        if ($roles) {
            $user = User::where('id', '=', $request->input('user_id'))->firstOrFail();

            if ($user->syncRoles($roles)) {
                return $this->success('messages.success');
            }
        }

        return $this->error();
    }
}
