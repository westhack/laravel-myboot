<?php echo "<?php\n";?>
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\{{$moduleName}}\Models;

use App\Models\BaseModel;

class {{$modelClassName}} extends BaseModel
{
    protected $table = '{{$table}}';

@if ($isSoftDelete)
    use \Illuminate\Database\Eloquent\SoftDeletes;
@endif

@if ($isLog)
    use \Spatie\Activitylog\Traits\LogsActivity;
    protected static $logAttributes = ['*'];
@endif

@if ($isFillable)

@if ($attributes)
    protected $fillable = [
@foreach($attributes as $key => $val)
        '{{$val}}',
@endforeach
    ];
@else
    protected $fillable = [];
@endif

@else

@if (empty($attributes))
    protected $guarded = [];
@else

    protected $guarded = [
@foreach($attributes as $key => $val)
        '{{$val}}',
@endforeach
    ];

@endif

@endif

@if ($appends)
    protected $appends = [
@foreach($appends as $key => $val)
        '{{$val}}',
@endforeach
    ];
@endif

@if ($hiddens)
    protected $hiddens = [
@foreach($hiddens as $key => $val)
        '{{$val}}',
@endforeach
    ];
@endif

@if ($relations)
@foreach($relations as $key => $val)
    public function {{$key}}()
    {
        return  {!!$val!!};
    }
@endforeach
@endif
}
