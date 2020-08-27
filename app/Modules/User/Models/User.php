<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Models;

use App\Foundation\Model\BatchUpdateTrait;
use App\Foundation\Model\SanitizesTrait;
use App\Foundation\Model\ScopeFirstOrErrorTrait;
use DateTimeInterface;
use EloquentSearch\SearchTrait;
use EloquentSearch\SortOrderTrait;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Modules\User\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable implements JWTSubject
{
    use BatchUpdateTrait;
    use Notifiable;
    use HasRoles;
    use SearchTrait;
    use SortOrderTrait;
    use SanitizesTrait;
    use LogsActivity;
    use ScopeFirstOrErrorTrait;

    protected static $logAttributes = ['*'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'wechat_id', 'wechat_session_key', 'pay_password', 'deleted_at'
    ];

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
        self::STATUS_NO  => '禁止',
        self::STATUS_YES => '正常',
    ];

    /**
     * Get the profile avatar URL attribute.
     *
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return get_avatar($value);
    }

    /**
     * @return string
     */
    public function getStatusTextAttribute()
    {
        $status = array_get($this->attributes, 'status', 1);

        return self::$mapStatus[$status];
    }

    /**
     * Get the oauth providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the User Info.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getPermissions()
    {
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values()->map(function ($permission) {
            return $permission->makeHidden(['pivot', 'guard_name', 'created_at', 'updated_at']);
        });
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
            'id',
            'username',
            'nickname',
            'phone',
            'status',
            'created_at',
        ];
    }
}
