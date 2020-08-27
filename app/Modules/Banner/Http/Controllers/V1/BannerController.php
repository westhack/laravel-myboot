<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Banner\Http\Controllers\V1;

use App\Http\Controllers\BaseApiController;
use App\Modules\Banner\Models\Banner;
use Illuminate\Database\Eloquent\Builder;

class BannerController extends BaseApiController
{
    protected $allowApi = true;

    protected $fields = [
        'id',
        'code',
        'type',
        'title',
        'jump',
        'image',
        'description',
        'sort_order',
        'status',
        'data',
    ];

    protected $includes = [
        'category' => [
            'fields' => ['id', 'name', 'code'],
        ],
    ];
    protected $pageSize = 100;
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
        $m = new Banner();

        return $m->newQuery();
    }
}
