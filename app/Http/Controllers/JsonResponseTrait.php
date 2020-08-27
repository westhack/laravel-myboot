<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */
namespace App\Http\Controllers;

use App\Constants\HttpResponseCode;

trait JsonResponseTrait
{
    /**
     * @param array $data
     *
     * @return \Illuminate\Http\Response
     */
    protected function responseJson($data)
    {
        return response($data);
    }

    /**
     * @param string $message
     * @param null   $data
     *
     * @return \Illuminate\Http\Response
     */
    protected function success($message = null, $data = null)
    {
        $response = [
            'code'      => HttpResponseCode::SUCCESS,
            'message'   => $message != '' && trans($message) != '' ? trans($message) : trans('messages.success'),
            'timestamp' => time(),
            'data'      => $data
        ];

        return response($response);
    }

    /**
     * @param string $message
     * @param null   $data
     *
     * @return \Illuminate\Http\Response
     */
    protected function error($message = null, $data = null)
    {
        $response = [
            'code'      => HttpResponseCode::ERROR,
            'message'   => $message != '' && trans($message) != '' ? trans($message) : trans('messages.error'),
            'timestamp' => time(),
            'data'      => $data
        ];

        return response($response);
    }
}
