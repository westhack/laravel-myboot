<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Core\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Yaml\Yaml;

class Dict extends BaseModel
{
    use SoftDeletes;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'value',
    ];

    protected $fillable = [
        'id',
        'title',
        'name',
        'data',
        'type',
        'delimiter',
        'description',
        'sort_order',
        'status',
        'created_at',
        'updated_at',
    ];

    public function getValueAttribute()
    {
        $type      = array_get($this->attributes, 'type', 1);
        $data      = array_get($this->attributes, 'data');
        $delimiter = array_get($this->attributes, 'delimiter') != '' ? $this->attributes['delimiter'] :'|';

        if (empty($data)) {
            return $data;
        }

        if ($type == 2) {
            return Yaml::parse($data);
        }

        $_value = [];
        $arr    = explode("\n", $data);

        if (is_array($arr)) {
            foreach ($arr as $key => $val) {
                $_arr = explode($delimiter, trim($val));

                if (count($_arr) >= 2) {
                    $_value[$_arr[1]] = $_arr[0];
                } else {
                    $_value[$_arr[0]] = $_arr[0];
                }
            }

            return $_value;
        }

        return $data;
    }
}
