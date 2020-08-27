<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\Article\Models;

use App\Models\BaseModel;

class ArticleCategory extends BaseModel
{
    protected $table = 'article_categorys';

    protected $hidden = [
        'updated_at',
        'deleted_at',
    ];
}
