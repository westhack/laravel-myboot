<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Core\Models;

use App\Foundation\Model\BatchUpdateTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;
use Symfony\Component\Yaml\Yaml;

class Config extends Model
{
    use NodeTrait;
    use SoftDeletes;
    use BatchUpdateTrait;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $appends = [
        'data',
        'rules',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [];

    public function getLftName()
    {
        return 'left';
    }

    public function getRgtName()
    {
        return 'right';
    }

    public function getParentIdName()
    {
        return 'parent_name';
    }

    public function setParentNameAttribute($value)
    {
        $this->setParentIdAttribute($value);
    }

    public function getMultipleAttribute($value)
    {
        if ($value === 1) {
            return true;
        }

        return false;
    }

    public function setMultipleAttribute($value)
    {
        if ($value == true || $value == 1) {
            $this->attributes['multiple'] = 1;
        } else {
            $this->attributes['multiple'] = 0;
        }
    }

    public function getValueAttribute($value)
    {
        $_value = json_decode($value, true);
        $error = json_last_error();

        if ($error === 0) {
            return $_value;
        }

        return $value;
    }

    public function getDataAttribute($val)
    {
        $val = array_get($this->attributes, 'data_source');
        if (empty($val)) {
            return '';
        }

        $ret = json_decode($val, true);
        if (!json_last_error()) {
            return $ret;
        }

        $ret = Yaml::parse($val);
        if (is_array($ret)) {
            return $ret;
        }

        $ret = [];
        $arr = explode("\n", $val);
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                $arr2 = explode('=', $v);
                if (is_array($arr2)) {
                    $ret[] = [
                        'label' => $arr2['1'],
                        'value' => $arr2['0'],
                    ];
                }
            }

            return $ret;
        }

        return $val;
    }

    public function getRulesAttribute($value)
    {
        $val = array_get($this->attributes, 'rules_source');
        if (empty($val)) {
            return '';
        }

        $arr = explode("\n", $val);

        return $arr;
    }

    public static function getTree($module = null, $root = null, $refresh = false)
    {
        $query = self::where('status', 1);

        if ($module != null) {
            $query->where('module', '=', $module);
        }

        $data = $query->get()->keyBy('name')->toArray();

        $result = [];
        if ($data) {
            $result = static::_tree($data, $root);
        }

        return $result;
    }

    /**
     * @param $data
     * @param null $parent
     * @return array
     */
    private static function _tree(&$data, $parent = null)
    {
        $result = [];
        $order = [];
        foreach ($data as $key => $item) {
            if ($item['parent_name'] === $parent) {
                $item['children'] = static::_tree($data, $key);

                $result[] = $item;
                $order[] = $item['sort_order'];
            }
        }
        if ($result !== []) {
            array_multisort($order, $result);
        }

        return $result;
    }

    public function fields()
    {
        return $this->hasMany(Config::class, 'parent_name', 'name')->orderBy('sort_order');
    }
}
