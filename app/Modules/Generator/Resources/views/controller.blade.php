<?php echo "<?php\n";?>
/**
* @link http://www.xinrennet.com/
*
* @copyright Copyright (c) 2020 Xinrennet Software LLC
* @author  Yao <yao@xinrennet.com>
*/

namespace App\Modules\{{$moduleName}}\Http\Controllers;

use App\Http\Controllers\BaseApiController;
use Illuminate\Database\Eloquent\Builder;

// Route::post('{{$apiList['list']}}', '{{$controllerClassName}}@index');
// Route::post('{{$apiList['create']}}', '{{$controllerClassName}}@create');
// Route::post('{{$apiList['update']}}', '{{$controllerClassName}}@update');
// Route::post('{{$apiList['delete']}}', '{{$controllerClassName}}@delete');
// Route::post('{{$apiList['detail']}}', '{{$controllerClassName}}@detail');
// Route::post('{{$apiList['all']}}', '{{$controllerClassName}}@all');

/**
* @OA\Post(
*      path="/api/{{$apiList['list']}}", tags = {"{{$controllerClassName}}"}, summary="列表",
*      @OA\Response(response="200",description="",),
* )
* @OA\Post(
*      path="/api/{{$apiList['create']}}", tags = {"{{$controllerClassName}}"}, summary="创建",
*      @OA\Response(response="200",description="",),
* )
* @OA\Post(
*      path="/api/{{$apiList['update']}}", tags = {"{{$controllerClassName}}"}, summary="更新",
*      @OA\Response(response="200",description="",),
* )
* @OA\Post(
*      path="/api/{{$apiList['delete']}}", tags = {"{{$controllerClassName}}"}, summary="删除",
*      @OA\Response(response="200",description="",),
* )
* @OA\Post(
*      path="/api/{{$apiList['detail']}}", tags = {"{{$controllerClassName}}"}, summary="详细",
*      @OA\Response(response="200",description="",),
* )
* @OA\Post(
*      path="/api/{{$apiList['all']}}", tags = {"{{$controllerClassName}}"}, summary="全部",
*      @OA\Response(response="200",description="",),
* )
*/
class {{$controllerClassName}} extends BaseApiController
{
    protected $allowApi = {{$allowApi == true ? 'true' : 'false'}};
    protected $pageSize = {{$pageSize ? $pageSize : 10}};
    protected $isGroupSearch = {{$isGroupSearch ? 'true' : 'false'}};
    protected $isUser = {{$isUser ? 'true' : 'false'}};
    protected $isMch = {{$isMch ? 'true' : 'false'}};
    protected $idName = '{{$idName ? $idName : 'id'}}';

@if ($fields)
    protected $fields    = [
@foreach($fields as $key => $field)
        '{{$field}}',
@endforeach
    ];
@else
    protected $fields    = ['*'];
@endif

@if ($includes)
    protected $includes = [
@foreach($includes as $key => $include)
        '{{$key}}' => [
            'fields' => [@foreach($include['fields'] as $field)'{{$field}}',@endforeach],
        ],
@endforeach
    ];
@endif

@if ($rules)
    protected $rules = [
    @foreach($rules as $key => $rule)
        '{{$key}}' => '{{$rule}}',
    @endforeach
    ];
@endif

@if ($withCounts)
    protected $rules = [
    @foreach($withCounts as $key => $withCount)
        '{{$withCount}}',
    @endforeach
    ];
@endif

@if ($rules)
    protected $filters = [
    @foreach($filters as $key => $filter)
        '{{$key}}' => '{{$filter}}',
    @endforeach
    ];
@endif

@if ($attributes)
    protected $attributes = [
    @foreach($attributes as $key => $attribute)
        '{{$key}}' => '{{$attribute}}',
    @endforeach
    ];
@endif

@if ($fillable)
    protected $fillable = [
    @foreach($fillable as $key => $val)
        '{{$val}}',
    @endforeach
    ];
@endif

@if ($sortOrder)
    private function getSortOrder()
    {
        return [
            'column' => '{{$sortOrder['column']}}',
            'order'  => '{{$sortOrder['order']}}',
        ];
    }
@endif

    public function getModel():  Builder
    {
        $m = new \{{$modelClassName}}();

        return $m->newQuery();
    }
}
