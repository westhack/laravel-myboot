<?php
/**
 * @link  http://www.xinrennet.com/
 *
 * @copyright  Copyright (c) 2020 Xinrennet Software LLC
 * @author    Yao <yao@xinrennet.com>
 */

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\BaseApiController;
use Illuminate\Database\Eloquent\Builder;

// Route::post('/core/activityLog/list', 'ActivityLogController@index');
// Route::post('/core/activityLog/create', 'ActivityLogController@create');
// Route::post('/core/activityLog/update', 'ActivityLogController@update');
// Route::post('/core/activityLog/delete', 'ActivityLogController@delete');
// Route::post('/core/activityLog/detail', 'ActivityLogController@detail');
// Route::post('/core/activityLog/all', 'ActivityLogController@all');

/**
 * @OA\Post(
 *      path="/core/activityLog/list", tags = {"ActivityLogController"}, summary="列表",
 *      @OA\Response(response="200",description="",),
 * )
 * @OA\Post(
 *      path="/core/activityLog/create", tags = {"ActivityLogController"}, summary="创建",
 *      @OA\Response(response="200",description="",),
 * )
 * @OA\Post(
 *      path="/core/activityLog/update", tags = {"ActivityLogController"}, summary="更新",
 *      @OA\Response(response="200",description="",),
 * )
 * @OA\Post(
 *      path="/core/activityLog/delete", tags = {"ActivityLogController"}, summary="删除",
 *      @OA\Response(response="200",description="",),
 * )
 * @OA\Post(
 *      path="/core/activityLog/detail", tags = {"ActivityLogController"}, summary="详细",
 *      @OA\Response(response="200",description="",),
 * )
 * @OA\Post(
 *      path="/core/activityLog/all", tags = {"ActivityLogController"}, summary="全部",
 *      @OA\Response(response="200",description="",),
 * )
 */
class ActivityLogController extends BaseApiController
{
    protected $allowApi = false;
    protected $pageSize = 10;
    protected $isGroupSearch = true;
    protected $isUser = false;
    protected $isMch = false;
    protected $idName = 'id';

    protected $fields    = [
        'id',
        'log_name',
        'description',
        'subject_id',
        'subject_type',
        'causer_id',
        'causer_type',
        'properties',
        'created_at',
        'updated_at',
    ];





    protected $attributes = [
        'id' => 'id',
        'log_name' => 'log_name',
        'description' => 'description',
        'subject_id' => 'subject_id',
        'subject_type' => 'subject_type',
        'causer_id' => 'causer_id',
        'causer_type' => 'causer_type',
        'properties' => 'properties',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];



    public function getModel():  Builder
    {
        $m = new \App\Modules\Core\Models\ActivityLog();

        return $m->newQuery();
    }
}
