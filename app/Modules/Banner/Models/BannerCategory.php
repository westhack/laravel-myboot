<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Banner\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class BannerCategory extends BaseModel
{
    use SoftDeletes;

    protected $table = 'banner_categorys';

    protected $fillable = [
        'id',
        'name',
        'code',
        'thumb',
        'description',
        'sort_order',
        'status',
    ];

    public $sortable = [
        'id' => 'descend',
        'sort_order',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function getThumbAttribute($value)
    {
        return $value == '' ? '' : asset($value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function banners()
    {
        return $this->hasMany(Banner::class, 'code', 'code');
    }
}
