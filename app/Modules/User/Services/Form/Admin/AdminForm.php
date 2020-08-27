<?php

namespace App\Modules\User\Services\Form\Admin;

use App\Modules\User\Models\Role;
use App\Modules\User\Models\User;

class AdminForm
{
    /**
     * @var User
     */
    protected $model;

    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $input
     *
     * @return User|bool
     */
    public function save(array $input)
    {
        $input['password'] = bcrypt($input['password']);

        if ($model = $this->model->create($input)) {

            if (request()->has('roles') != '') {
                $roles = Role::whereIn('id', request()->input('roles') )->pluck('name');
                if ($roles) {
                    $model->syncRoles($roles);
                }
            }

            return $model;
        }

        return false;
    }

    /**
     * @param array $input
     *
     * @return bool|User
     */
    public function update(array $input)
    {
        $id = array_get($input, 'id');

        if (array_get($input, 'password') == '') {
            unset($input['password']);
        } else {
            $input['password'] = bcrypt($input['password']);
        }

        if (is_array($id)) {
            $data = $input;

            unset($data['id']);

            return $model = $this->model->where('id', 'in', $id)->update($data);
        } else {
            $model = $this->find($id);

            if ($model->fill($input)->save()) {
                if (request()->has('roles') != '') {
                    $roles = Role::whereIn('id', request()->input('roles') )->pluck('name');
                    if ($roles) {
                        $model->syncRoles($roles);
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
     * @return User
     */
    public function find($id)
    {
        return $this->model->whereId($id)->firstOrFail();
    }
}
