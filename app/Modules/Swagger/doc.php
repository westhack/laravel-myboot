<?php
/**
 *
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="接口文档",
 *         description="接口文档",
 *         termsOfService="http://laravel-vue.xinrennet.com",
 *         @OA\Contact(
 *             email="yao@xinrennet.com"
 *         ),
 *     ),
 *     @OA\Server(
 *         description="线上测试环境",
 *         url="http://laravel-vue.xinrennet.com"
 *     ),
 *     @OA\Server(
 *         description="本地开发环境",
 *         url="http://127.0.0.1:8000"
 *     ),
 * )
 *
 * @OA\Response(
 *      response="BaseSuccessResponse",
 *      description="the basic error response",
 *      @OA\JsonContent(
 *          @OA\Property( property="code", type="integer", description="响应状态", example="1"),
 *          @OA\Property( property="message", type="string", description="响应消息", example="Success"),
 *      )
 * )
 *
 * @OA\Response(
 *      response="BaseErrorResponse",
 *      description="the basic error response",
 *      @OA\JsonContent(
 *          @OA\Property( property="code", type="integer", description="响应状态", example="0"),
 *          @OA\Property( property="message", type="string", description="响应消息", example="Error"),
 *      )
 * )
 *
 * @OA\Schema(
 *      schema="BaseSuccessResponse",
 *      type="object",
 *      description="响应实体，响应结果统一使用该结构",
 *      title="响应实体",
 *      @OA\Property(property="code",type="string",description="响应代码", example="200"),
 *      @OA\Property(property="message", type="string", description="响应结果提示", example="SUCCESS")
 * )
 * @OA\Schema(
 *      schema="BaseErrorResponse",
 *      type="object",
 *      description="响应实体，响应结果统一使用该结构",
 *      title="响应实体",
 *      @OA\Property(property="code",type="string",description="响应代码", example="500"),
 *      @OA\Property(property="message", type="string", description="响应结果提示", example="ERROR")
 * )
 */
