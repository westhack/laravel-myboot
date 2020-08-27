<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Banner\Models;

use App\Models\BaseModel;

class Banner extends BaseModel
{
    protected $table = 'banners';

    protected $fillable = [
        'id',
        'code',
        'title',
        'jump',
        'image',
        'description',
        'sort_order',
        'status',
        'mch_id',
        'data',
        'created_at',
        'updated_at',
    ];

    protected $appends = [];

    public function getJumpAttribute($value)
    {
        return (string) $value;
    }

    public function getTitleAttribute($value)
    {
        return (string) $value;
    }

    public function getImageAttribute($value)
    {
        return $value == '' ? '' : asset($value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }

    public function category()
    {
        return $this->hasOne(BannerCategory::class, 'code', 'code');
    }
}
