<?php

namespace App\Modules\Core\Services\Form;

use App\Foundation\Requests\BaseFormRequest;

class DictFormRequest extends BaseFormRequest
{
    public function filters()
    {
        return [
            'title' => 'trim',
            'name'  => 'trim',
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
        return [
            'title' => 'required|maxlength:255',
            'name'  => [
                'required',
                'alpha_dash',
                'maxlength:255',
            ],
            'data'  => 'required',
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
            'title'       => '名称',
            'name'        => '键',
            'value'       => '值',
            'data'        => '数据',
            'description' => '描述',
            'delimiter'   => '分隔符',
            'sort_order'  => '排序',
        ];
    }
}
