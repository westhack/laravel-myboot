<?php

namespace App\Modules\User\Services\Form\User;

use App\Foundation\Requests\BaseFormRequest;
use App\Modules\User\Models\User;

class UserFormRequest extends BaseFormRequest
{

    public function filters()
    {
        return [
            'username' => 'trim',
            'nickname' => 'trim',
            'password' => 'trim',
            'phone'    => 'trim',
            'email'    => 'trim',
            'status'   => 'boolToInt',
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
            'nickname'       => 'maxlength:20',
            'password'    => [
                function ($attribute, $value, $fail) use ($_this) {
                    if (request()->input('id') == 0 && $value == '') {
                        return $fail(trans('validation.required', [':attribute' => $_this->attributes()[$attribute]]));
                    } else {
                        if ($value != '' && mb_strlen($value) > 20) {
                            return $fail(trans('validation.max.string', [':attribute' => $_this->attributes()[$attribute], ':max' => 20]));
                        }
                    }

                    return true;
                }
            ],
            'phone'       => [
                'phone',
                function ($attribute, $value, $fail) use ($_this) {
                    if (request()->input('id') == 0) {
                        if ($value == '') {
                            return $fail(trans('validation.required', [':attribute' => $_this->attributes()[$attribute]]));
                        }
                    }

                    if (User::where('phone', $value)->where('id', '!=', request()->input('id'))->where('is_backend', 0)->exists()) {
                        return $fail(trans('validation.exists', [':attribute' => $_this->attributes()[$attribute]]));
                    }

                    return true;
                }
            ],
            'status'      => 'in:1,0',
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
            'id'       => 'ID',
            'nickname' => '昵称',
            'email'    => '邮箱',
            'phone'    => '手机号',
            'status'   => '状态',
            'password' => '密码',
        ];
    }
}
