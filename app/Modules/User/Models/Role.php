<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Models;

class Role extends \Spatie\Permission\Models\Role
{
    protected $hidden = [
        'pivot',
        'created_at',
        'updated_at',
    ];
}
