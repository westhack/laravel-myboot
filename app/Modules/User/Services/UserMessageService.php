<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\UserMessage;

class UserMessageService
{
    /**
     * @param $user_id 接受用户
     * @param $title 主题
     * @param $msg 消息内容
     * @param $from_user_id 发送者
     * @param $type 消息类型
     * @param $is_backend 是否后台消息
     * @param string $data_type 数据类型
     * @param null $data_id 数据id
     * @param string $data 数据
     * @return mixed
     */
    public static function send($user_id, $title, $msg, $from_user_id, $type, $is_backend, $data_type = 'msg', $data_id = null, $data = null)
    {
        return UserMessage::create([
            'user_id'      => $user_id,
            'title'        => $title,
            'message'      => $msg,
            'from_user_id' => $from_user_id,
            'type'         => $type,
            'is_backend'   => $is_backend ? 1: 0,
            'data_type'    => $data_type,
            'data_id'      => $data_id,
            'data'         => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
