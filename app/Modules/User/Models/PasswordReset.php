<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Models;

use App\Models\BaseModel;

class PasswordReset extends BaseModel
{
    protected $primaryKey = 'uuid';

    protected $guarded = [];

    public $timestamps = false;

    protected $table = 'password_resets';
}
