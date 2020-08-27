<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Config;
use App\Modules\Core\Models\Module;
use App\Modules\Core\Services\Form\ConfigForm;
use App\Modules\Core\Services\Form\ConfigFormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConfigController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $modules = Module::orderBy('sort_order')->get()->toArray();

        $data = [];
        foreach ($modules as $key => $module) {
            $module['group'] = true;
            $module['value'] = $module['name'];
            $module['label'] = $module['description'];

            $module['children'] = Config::where('module', '=', strtolower($module['name']))
                ->whereNull('parent_name')
                ->with(
                    [
                        'fields' => static function ($query) use ($request) {
                            if (! $request->has('label') || $request->input('label') == '') {
                                return;
                            }

                            $query->where('label', 'like', '%' . $request->input('label') . '%');
                        },
                    ]
                )->orderBy('sort_order')->get();

            $data[] = $module;
        }

        return $this->success('messages.success', ['modules' => $data]);
    }

    /**
     * @param ConfigFormRequest $request
     * @param ConfigForm        $form
     *
     * @return Response
     */
    public function create(ConfigFormRequest $request, ConfigForm $form)
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
     * @param ConfigFormRequest $request
     * @param ConfigForm        $form
     *
     * @return Response
     */
    public function update(ConfigFormRequest $request, ConfigForm $form)
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

            if ($res = Config::batchUpdate($data, 'name') == true) {
                \App\Modules\Core\Support\Facades\Config::writeModuleAll();

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

        $config = Config::where('name', '=', $name)->firstOrFail();

        if (isset($config->fields) && count($config->fields) > 0) {
            return $this->error('messages.delete_children_first');
        }

        if ($config->delete()) {
            \App\Modules\Core\Support\Facades\Config::writeModuleAll();

            return $this->success('messages.success');
        }

        return $this->error('messages.error');
    }
}
