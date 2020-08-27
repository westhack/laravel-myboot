<?php

namespace App\Constants;

class HttpResponseCode
{
    public const SUCCESS            = 200; // 成功
    public const ERROR              = 500; // 失败
    public const BAD_REQUEST        = 400; // 请求错误
    public const METHOD_NOT_ALLOWED = 405; // 方法不允许
    public const UNAUTHENTICATED    = 401; // 未认证
    public const UNAUTHORIZED       = 403; // 未授权
    public const NOT_FOUND          = 404; // 内容不存在
}
