<?php

namespace App\Foundation\Model;

use App\Constants\HttpResponseCode;

trait ScopeFirstOrErrorTrait
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $message
     *
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    public function scopeFirstOrError($query, $message = null)
    {
        $code = HttpResponseCode::ERROR;

        if ($message == null) {
            $message = trans('messages.no_data');
        } else {
            $message = trans($message);
        }

        if (! is_null($model = $query->first())) {
            return $model;
        }

        return response()->json([ 'code' => $code, 'message' => $message ], 200)->throwResponse();
    }
}
