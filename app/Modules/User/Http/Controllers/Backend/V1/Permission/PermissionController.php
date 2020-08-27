<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Http\Controllers\Backend\V1\Permission;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\Form\Permission\PermissionForm;
use App\Modules\User\Helpers\RouteHelper;
use App\Modules\User\Models\Permission;
use App\Modules\User\Services\Form\Permission\PermissionFormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

class PermissionController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/permission/list", tags = {"后端-权限"}, summary="权限列表",
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
        Permission::fixTree();
        $tree = Permission::orderBy('sort_order')->descendantsOf('1')->toTree()->toArray();

        return $this->success(
            'messages.success',
            [
                'items' => $tree,
            ]
        );
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/permission/create", tags = {"后端-权限"}, summary="权限添加",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param PermissionFormRequest $request
     * @param PermissionForm $form
     *
     * @return Response
     */
    public function create(PermissionFormRequest $request, PermissionForm $form)
    {
        if ($model = $form->save($request->onlyInput())) {
            return $this->success(
                'messages.success',
                [
                    'permission' => $model
                ]
            );
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/permission/update", tags = {"后端-权限"}, summary="权限更新",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param PermissionFormRequest $request
     * @param PermissionForm $form
     *
     * @return Response
     */
    public function update(PermissionFormRequest $request, PermissionForm $form)
    {
        if ($model = $form->update($request->onlyInput())) {
            return $this->success(
                'messages.success',
                [
                    'permission' => $model
                ]
            );
        }

        return $this->error();
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/permission/delete", tags = {"后端-权限"}, summary="权限删除",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     * @return Response|mixed
     * @throws \Throwable
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');

        $ids = array_wrap($id);

        if (count($ids) == 0) {
            return $this->error('messages.select_delete_record');
        }

        return \DB::transaction(function () use ($ids){
            foreach ($ids as $key => $val) {
                if ($val == 1) {
                    continue;
                }

                $model = Permission::where('id', '=', $val)->firstOrFail();

                if (count($model->descendants) > 0) {
                    return $this->error('messages.delete_children_first');
                }

                $model = Permission::where('id', '=', $val)->firstOrFail();

                if (count($model->descendants) > 0) {
                    return $this->error('messages.delete_children_first');
                }

                if (!$model->delete()) {
                    return $this->error();
                }

            }

            return $this->success();
        });
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/permission/routes", tags = {"后端-权限"}, summary="权限接口路由",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @return Response
     */
    public function routes()
    {
        $routeNames = RouteHelper::getRouteNames();
        $ret = [];
        foreach ($routeNames as $key => $val) {
            $ret[$key] = [
              'label' => $val['route'],
              'value' => $val['route'],
            ];
        }

        return $this->success('messages.success',  [
            'items' => $ret
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/backend/user/v1/permission/all", tags = {"后端-权限"}, summary="权限下拉列表",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function all(Request $request)
    {
        $items = Permission::orderBy('sort_order')
            ->select('title as label', 'id as value', 'id', 'left', 'right', 'parent_id', 'sort_order')
            ->descendantsOf('1')->toTree()->toArray();

        return $this->success('messages.success', [
            'items' => $items,
        ]);
    }

    public function export()
    {
        Permission::fixTree();
        $tree = Permission::toTree()->toArray();

        $fileName = "menus.yaml";

        header("Content-type:text/yaml");
        header("Content-Disposition:attachment;filename=" . $fileName);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo Yaml::dump($tree, 10, 2);
        exit;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Throwable
     */
    public function import(Request $request)
    {
        $files = array_values($request->file());
        if ($files) {
            $data = file_get_contents($files[0]->getPathname());
            $data = Yaml::parse($data);

            $children = array_get($data, 'children');
            if ($children) {
                $ret = Permission::create($data);
            } else {
                $ret = \DB::transaction(function () use ($data) {
                    $ret = [];
                    foreach ($data as $key => $val) {
                        $ret[] = Permission::create($val);
                    }

                    return $ret;
                });
            }

            return $this->success('messages.success', [
                'items' => $ret,
            ]);
        }

        return $this->error();
    }
}
