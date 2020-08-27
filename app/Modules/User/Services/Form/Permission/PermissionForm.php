<?php

namespace App\Modules\User\Services\Form\Permission;

use App\Modules\User\Models\Permission;

class PermissionForm
{
    protected $buttons = [
        'add' => ['value' => 'add', 'label' => '新增'],
        'delete' => ['value' => 'delete', 'label' => '删除'],
        'edit' => ['value' => 'edit', 'label' => '修改'],
        'query' => ['value' => 'query', 'label' => '查询'],
        'get' => ['value' => 'get', 'label' => '详情'],
        'enable' => ['value' => 'enable', 'label' => '启用'],
        'disable' => ['value' => 'disable', 'label' => '禁用'],
        'import' => ['value' => 'import', 'label' => '导入'],
        'export' => ['value' => 'export', 'label' => '导出'],
    ];

    /**
     * @var Permission
     */
    protected $model;

    /**
     * @param Permission $model
     */
    public function __construct(Permission $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $input
     *
     * @return Permission|bool
     */
    public function save(array $input)
    {
        if ($input['name'] == '') {
            $input['name'] = lcfirst($input['component']);
        }

        if (!empty($input['route']) && is_array($input['route'])) {
            $input['route'] = implode(',', $input['route']);
        } else {
            $input['route'] = '';
        }
        $buttons = $input['buttons'];
        unset($input['buttons']);
        $res = $this->model->create($input);
        if ($res) {
            if (!empty($buttons)) {
                foreach ($buttons as $button) {
                    if (isset($this->buttons[$button])) {
                        Permission::create([
                            'parent_id' => $res['id'],
                            'name'      => $input['name'] . $this->buttons[$button]['value'],
                            'title'     => $this->buttons[$button]['label'],
                            'is_menu'   => 0,
                        ]);
                    }
                }
            }

            return $res;
        }

        return false;
    }

    /**
     * @param array $input
     *
     * @return bool|Permission
     */
    public function update(array $input)
    {
        $id = array_get($input, 'id');
        $buttons = $input['buttons'];
        unset($input['buttons']);
        if (!empty($input['route']) && is_array($input['route'])) {
            $input['route'] = implode(',', $input['route']);
        } else {
            $input['route'] = '';
        }

        if (is_array($id)) {
            $data = $input;
            unset($data['id']);

            return $model = $this->model->where('id', 'in', $id)->update($data);
        } else {
            $model = $this->find($id);
            $parent = Permission::where('id', '=', $input['parent_id'])->first();
            if ($parent) {
                if ($parent->isDescendantOf($model)) {
                    return false;
                }
            }

            if ($input['is_menu'] == 1) {
                $input['hidden'] = 0;
            } else {
                $input['hidden'] = 1;
            }

            $res = $model->fill($input)->save();
            if ($res) {
                if (!empty($buttons)) {
                    $children = Permission::where('parent_id', $model->id)->where('is_menu', 0)->get();
                    $names = [];
                    if ($children) {
                        foreach ($children as $child) {
                            if (!in_array($child->name, $buttons)) {
                                $child->delete();
                            } else {
                                $names[] = $child->name;
                            }
                        }
                    }

                    foreach ($buttons as $button) {
                        if (!in_array($button, $names)) {
                            $b = str_replace_first($input['name'], '', $button);
                            Permission::create([
                                'parent_id' => $model->id,
                                'name'      => $button,
                                'title'     => $this->buttons[$b]['label'],
                                'is_menu'   => 0,
                            ]);
                        }
                    }
                }

                return $model;
            }

            return false;
        }
    }

    /**
     * @param $id
     *
     * @return Permission
     */
    public function find($id)
    {
        return $this->model->whereId($id)->firstOrFail();
    }
}
