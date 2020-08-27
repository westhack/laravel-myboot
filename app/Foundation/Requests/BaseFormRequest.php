<?php

namespace App\Foundation\Requests;

use App\Constants\HttpResponseCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Waavi\Sanitizer\Laravel\SanitizesInput;

class BaseFormRequest extends FormRequest
{
    use SanitizesInput;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function customFilters() {
        return [];
    }

    /**
     * @param null $key
     * @param null $default
     *
     * @return array|string|null
     */
    public function onlyInput($key = null, $default = null)
    {
        $attributes = array_keys($this->attributes());

        return data_get(
            self::only($attributes), $key, $default
        );
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        response()->json(
            [
                'code'    => HttpResponseCode::ERROR,
                'message' => $validator->errors()->first(),
            ],
            200
        )->throwResponse();
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        response()->json(
            [
                'code'    => HttpResponseCode::UNAUTHORIZED,
                'message' => trans('messages.action_unauthorized'),
            ],
            200
        )->throwResponse();
    }
}
