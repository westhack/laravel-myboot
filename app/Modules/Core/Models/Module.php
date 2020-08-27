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

class Module extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [];

    public function configs()
    {
        return $this->hasMany(Config::class, 'module', 'name')->orderBy('sort_order');
    }
}
