<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Article\Http\Controllers\Backend;

use App\Http\Controllers\BaseApiController;
use App\Modules\Article\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends BaseApiController
{
    protected $allowApi = true;
    protected $isMch = true;

    protected $fields = ['*'];

    protected $includes = [];

    protected $pageSize = 10;
    protected $rules    = [
        'title' => 'required',
        'image' => 'required',
    ];

    protected $filters = [
        'title'       => 'trim|strip_tags',
        'description' => 'strip_tags',
        'sort_order'  => 'digit',
        'status'      => 'boolToInt',
    ];

    protected $attributes = [];

    public function fillable()
    {
        return $this->getModel()->getModel()->getFillable();
    }

    private function getSortOrder()
    {
        return [
            'column' => 'sort_order',
            'order'  => 'ascend',
        ];
    }

    public function getModel():  Builder
    {
        $m = new ArticleCategory();

        return $m->newQuery();
    }
}
