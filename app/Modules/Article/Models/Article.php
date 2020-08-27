<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Article\Models;

use App\Models\BaseModel;

class Article extends BaseModel
{
    protected $table = 'articles';

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];

    protected $guarded = [];

    public function category()
    {
        return $this->hasOne(ArticleCategory::class, 'id', 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '=', 1);
    }
}
