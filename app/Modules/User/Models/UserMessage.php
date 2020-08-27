<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Models;

use App\Models\BaseModel;
use EloquentFilter\Filterable;

class UserMessage extends BaseModel
{
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_text'
    ];

    const STATUS_YES = 1;
    const STATUS_NO  = 0;

    public static $mapStatus = [
        self::STATUS_NO  => '未读',
        self::STATUS_YES => '已读',
    ];

    /**
     * @return string
     */
    public function getStatusTextAttribute()
    {
        $status = array_get($this->attributes, 'status', 1);

        return self::$mapStatus[$status];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->withDefault([
            'avatar' => '',
            'nickname' => '系统'
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fromUser()
    {
        return $this->hasOne(User::class, 'id', 'from_user_id')->withDefault([
            'avatar' => '',
            'nickname' => '系统'
        ]);
    }

    public function getSortable() {
        return [
            'id' => $this->getSortOrderDesc(),
            'created_at' => $this->getSortOrderDesc()
        ];
    }

    public function getSearchable()
    {
        return [
            'status'
        ];
    }
}
