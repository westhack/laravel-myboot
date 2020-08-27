<?php

namespace App\Modules\User\Services\Form\Permission;

use App\Foundation\Requests\BaseFormRequest;
use App\Modules\User\Models\Permission;

class PermissionFormRequest extends BaseFormRequest
{
    public function filters()
    {
        return [
            'title'         => 'trim',
            'name'          => 'trim',
            'parent_id'     => 'trim',
            'path'          => 'trim',
            'route'         => 'trim',
            'status'        => 'boolToInt',
            'keep_alive'    => 'boolToInt',
            'hidden'        => 'boolToInt',
            'is_menu'       => 'boolToInt',
            'is_permission' => 'boolToInt',
            'hidden_header_content' => 'boolToInt',
            'default_path'  => 'trim',
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
            'title'     => 'required|maxlength:255',
            'name'      => 'required|maxlength:255',
            'parent_id' => [
                static function ($attribute, $value, $fail) use ($_this) {
                    if ($value == request('id')) {
                        return $fail(trans('validation.exists', [':attribute' => $_this->attributes()[$attribute]]));
                    }

                    if ($value != '' && ! Permission::where('id', '=', $value)->exists()) {
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
            'id'            => 'ID',
            'route'         => '后端路由',
            'path'          => '前端路由',
            'parent_id'     => '上级',
            'component'     => '组件',
            'name'          => '名称',
            'redirect'      => '跳转路由',
            'icon'          => '图标',
            'title'         => '标题',
            'hidden'        => '是否隐藏',
            'keep_alive'    => '是否持久化',
            'is_menu'       => '是否菜单',
            'is_permission' => '是否权限',
            'sort_order'    => '排序',
            'status'        => '状态',
            'default_path'  => '默认路由',
            'buttons'       => '操作按钮',
            'hidden_header_content' => '是否隐藏头部',
        ];
    }
}
