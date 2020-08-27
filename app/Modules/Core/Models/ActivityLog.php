<?php
/**
 * @link  http://www.xinrennet.com/
 *
 * @copyright  Copyright (c) 2020 Xinrennet Software LLC
 * @author    Yao <yao@xinrennet.com>
 */

namespace App\Modules\Core\Models;

use App\Models\BaseModel;
use DateTimeInterface;
use EloquentSearch\SearchTrait;
use EloquentSearch\SortOrderTrait;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use SearchTrait;
    use SortOrderTrait;

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getSortable() {
        return [
            'id' => $this->getSortOrderDesc(),
            'created_at' => $this->getSortOrderDesc()
        ];
    }
}
