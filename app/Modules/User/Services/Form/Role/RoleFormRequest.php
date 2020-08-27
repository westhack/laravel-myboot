<?php

namespace App\Modules\User\Services\Form\Role;

use App\Foundation\Requests\BaseFormRequest;
use App\Modules\User\Models\Role;

class RoleFormRequest extends BaseFormRequest
{
    public function filters()
    {
        return [
            'title'       => 'trim',
            'name'        => 'trim',
            'description' => 'trim',
            'guard_name'  => 'trim',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $_this = $this;

        return [
            'guard_name'       => 'required',
            'description'      => 'maxlength:255',
            'name' => [
                static function ($attribute, $value, $fail) use ($_this) {
                    if ($value != '' && Role::where('name', '=', $value)->where('id', '!=', request('id'))->exists()) {
                        return $fail(trans('validation.exists', [':attribute' => $_this->attributes()[$attribute]]));
                    }
                },
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'id'          => 'ID',
            'name'        => '角色名',
            'title'       => '名称',
            'description' => '简介',
            'guard_name'  => '守卫',
            'permissions' => '权限',
        ];
    }
}
