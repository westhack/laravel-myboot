<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Models\Filters;

use EloquentFilter\ModelFilter;

class UserMessageFilter extends ModelFilter
{
    protected $drop_id = false;

    protected $camel_cased_methods = true;

    public function setup()
    {
        $this->where('user_id', auth()->user()->id);
    }

    public function status($status)
    {
        if ($status == 1) {
            $this->where('status', 1);
        } else {
            $this->where('status', 0);
        }

        return $this;
    }
}
