<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Models;

use App\Foundation\Helpers\ArrayHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class Permission extends \Spatie\Permission\Models\Permission
{
    use NodeTrait;
    use SoftDeletes;

    protected $hidden = [
        'created_at',
        'updated_at',
        'left',
        'right',
        'pivot',
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

    public function getPathAttribute($value)
    {
        return $value != '' ? $value : '';
    }

    public function getComponentAttribute($value)
    {
        return $value != '' ? $value : '';
    }

    public function getRouteAttribute($value)
    {
        return $value != '' ? explode(',', $value) : [];
    }

    public function getStatusAttribute($value)
    {
        return $value == 1 ? true : false;
    }

    public function getKeepAliveAttribute($value)
    {
        return $value == 1 ? true : false;
    }

    public function getHiddenAttribute($value)
    {
        return $value == 1 ? true : false;
    }

    public function getHiddenHeaderContentAttribute($value)
    {
        return $value == 1 ? true : false;
    }

    public function getIsMenuAttribute($value)
    {
        return  $value == 1 ? true : false;
    }

    public static function getTree($permissions, $root = null, $refresh = false)
    {
        if ($permissions) {
            foreach ($permissions as $key => $permission) {
                if ($permission['is_menu'] == false) {
                    unset($permissions[$key]);
                }
            }
            $data = ArrayHelper::index($permissions, 'id');
        } else {
            $data = self::where('status', 1)->where('is_menu', 1)->get()->keyBy('id')->toArray();
        }

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
            if ($item['parent_id'] == $parent) {
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
}
