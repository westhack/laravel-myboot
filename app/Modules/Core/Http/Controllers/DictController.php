<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Dict;
use App\Modules\Core\Services\Form\DictForm;
use App\Modules\Core\Services\Form\DictFormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DictController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $page_size = $request->input('pageSize', 10);

        $paginate = Dict::paginate($page_size);

        return $this->success(
            'messages.success',
            [
                'items'       => $paginate->items(),
                'lastPage'    => $paginate->lastPage(),
                'total'       => $paginate->total(),
                'currentPage' => $paginate->currentPage(),
            ]
        );
    }

    /**
     * @param DictFormRequest $request
     * @param DictForm        $form
     *
     * @return Response
     */
    public function create(DictFormRequest $request, DictForm $form)
    {
        if ($model = $form->save($request->onlyInput())) {
            return $this->success(
                'messages.success',
                [
                    'config' => $model,
                ]
            );
        }

        return $this->error();
    }

    /**
     * @param DictFormRequest $request
     * @param DictForm        $form
     *
     * @return Response
     */
    public function update(DictFormRequest $request, DictForm $form)
    {
        if ($model = $form->update($request->onlyInput())) {
            return $this->success(
                'messages.success',
                [
                    'config' => $model,
                ]
            );
        }

        return $this->error();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function batchUpdateValue(Request $request)
    {
        $req = $request->input();

        $data = [];
        if (is_array($req)) {
            foreach ($req as $name => $value) {
                $data[] = [
                    'name'  => $name,
                    'value' => json_encode($value, JSON_UNESCAPED_UNICODE),
                ];
            }

            if ($res = Dict::batchUpdate($data, 'name') === true) {
                return $this->success('messages.success');
            }

            return $this->error('messages.no_action_required');
        }

        return $this->error();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function delete(Request $request)
    {
        $name = $request->input('name');

        $config = Dict::where('name', '=', $name)->first();

        if (count($config->fields) > 0) {
            return $this->error('messages.delete_children_first');
        }

        if ($config->delete()) {
            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @OA\Post(
     *      path="/api/core/v1/dict/all", tags = {"后端-字典"}, summary="系统所有字典",
     *      @OA\Parameter(
     *          in="header", name="Authorization", description="用户令牌", required=true,
     *          @OA\Schema(type="string", example="Bearer b43c5b13522193473deb3f37a3c2f50cbc5e14b6")
     *      ),
     *      @OA\Response(response="200",description="")
     * )
     */
    public function all(Request $request)
    {
        $items = Dict::where('status', '=', 1)->orderBy('sort_order')->get()->pluck('value', 'name')->toArray();

        return $this->success('messages.success', [
            'items' => $items,
        ]);
    }
}
