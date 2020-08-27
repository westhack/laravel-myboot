<?php

namespace App\Modules\User\Services\Form\Role;

use App\Modules\User\Models\Permission;
use App\Modules\User\Models\Role;

class RoleForm
{
    /**
     * @var Role
     */
    protected $model;

    /**
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $input
     *
     * @return Role|bool
     */
    public function save(array $input)
    {
        return $this->model->create($input);
    }

    /**
     * @param array $input
     *
     * @return bool|Role
     */
    public function update(array $input)
    {
        $id = array_pull($input, 'id');
        $permissions = array_pull($input, 'permissions');

        if (is_array($id)) {
            $data = $input;

            return $model = $this->model->where('id', 'in', $id)->update($data);
        } else {
            $model = $this->find($id);

            if($model->fill($input)->save()) {
                array_push($permissions, 1);
                $model->syncPermissions(Permission::whereIn('id', $permissions)->get());

                return $model;
            }
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return Role
     */
    public function find($id)
    {
        return $this->model->whereId($id)->firstOrFail();
    }
}
