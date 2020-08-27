<?php

namespace App\Modules\Core\Services\Form;

use App\Foundation\Requests\BaseFormRequest;
use App\Modules\Core\Models\Config;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;

class ConfigFormRequest extends BaseFormRequest
{

    public function filters()
    {
        return [
            'label' => 'trim',
            'name'  => 'trim|tableize',
//            'data'  => 'json',
//            'rules'  => 'json',
            'status'  => 'boolToInt',
            'multiple'  => 'boolToInt',
        ];
    }

    public function customFilters() {

        return array_merge(
            parent::customFilters(),
            [
                'tableize' => function($value, $options = []) {
                    $inflector = InflectorFactory::create()->build();

                    return $inflector->tableize($value);
                }
            ]
        );
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
            'label'       => 'required|maxlength:255',
            'parent_name' => [
                function ($attribute, $value, $fail) use ($_this) {
                    if (! empty($value) && !Config::where('name', '=', $value)->exists()) {
                        return $fail(trans('validation.exists', [':attribute' => $_this->attributes()[$attribute]]));
                    }
                },
            ],
            'name'        => [
                'required',
                'alpha_dash',
                'maxlength:255',
                 function ($attribute, $value, $fail) use ($_this) {
                    if (Config::where('name', '=', $value)->where('id', '!=', request()->input('id'))->exists()) {
                        return $fail(trans('validation.exists', [':attribute' => $_this->attributes()[$attribute]]));
                    }
                },
            ],
            'type'        => 'required',
            'sort_order'  => 'numeric',
            'status'      => 'in:1,0',
            'module'      => 'required',
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
            'id'           => 'ID',
            'parent_name'  => '上级',
            'label'        => '名称',
            'name'         => '键',
            'value'        => '值',
            'type'         => '类型',
            'sort_order'   => '排序',
            'status'       => '状态',
            'module'       => '模块',
            'data_source'  => '数据',
            'rules_source' => '规则',
        ];
    }
}
