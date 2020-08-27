<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Banner\Http\Controllers\V1;

use App\Http\Controllers\BaseApiController;
use App\Modules\Banner\Models\BannerCategory;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends BaseApiController
{
    protected $allowApi = true;

    protected $fields = ['id', 'name', 'status', 'code', 'thumb', 'description'];

    protected $includes = [
        'banners' => [
            'fields' => ['id', 'mch_id', 'code', 'image', 'jump', 'data']
        ],
    ];

    protected $rules = [
        'name' => 'required',
        'code' => 'required',
    ];

    protected $filters = [
        'name'       => 'trim|strip_tags',
        'sort_order' => 'digit',
        'status'     => 'boolToInt',
    ];

    protected $attributes = [];

    public function fillable()
    {
        return $this->getModel()->getModel()->getFillable();
    }

    public function getModel(): Builder
    {
        $m = new BannerCategory();

        return $m->newQuery();
    }
}
