<?php

namespace  App\Modules\Captcha\Models;

use App\Models\BaseModel;
use EloquentSearch\SearchTrait;

class SmsCode extends BaseModel
{
    use SearchTrait;

    protected $table = 'sms_code';

    protected $guarded = [];

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

}
