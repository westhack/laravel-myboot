<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use App\Modules\User\Models\UserInfo;

class UserService
{
    public static function info($user)
    {
        /** @var \App\Modules\User\Models\User $user **/
        if (is_string($user) || is_int($user)) {
            $user = User::where('id', '=', $user)->firstOrFail();
        }

        $roles       = $user->getRoleNames();
        $permissions = $user->getPermissions();

        unset($user->roles);
        unset($user->permissions);

        $user->setAttribute('roles', $roles);
        $user->setAttribute('permissions', $permissions);

        return $user;
    }

    public static function user($user)
    {
        if (is_string($user) || is_int($user)) {
            $user = User::where('id', '=', $user)->firstOrFail();
        }

        return $user;
    }

    /**
     * 更新用户信息
     *
     * @param array $params
     * @param User $user
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function update($params, $user)
    {
        \DB::beginTransaction();
        $request = request();
        try {
            if (!empty($params['avatar'])) $user->avatar = $params['avatar'];
            if (!empty($params['nickname'])) $user->nickname = $params['nickname'];

            $userInfo = UserInfo::where('user_id', '=', $user->id)->first();
            if (!$userInfo) {
                UserInfo::create([
                    'user_id'         => $user->id,
                    'last_login_time' => date('Y-m-d H:i;s'),
                    'last_login_ip'   => $request->getClientIp(),
                    'reg_ip'          => $request->getClientIp(),
                    'reg_time'        => date('Y-m-d H:i;s'),
                ]);
            }

            if ($user->save()) {
                \DB::commit();

                return true;
            }
        } catch (\Exception $exception) {
            \DB::rollBack();
            logger($exception);
        }

        return false;
    }
}
