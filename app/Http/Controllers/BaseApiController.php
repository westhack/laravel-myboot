<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Http\Controllers;

use App\Constants\HttpResponseCode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @example
 *  protected $fields = ['id', 'mch_id', 'code', 'image', 'jump', 'data'];
 *  protected $includes = [
 * 'merchant' => [
 *      'fields' => ['id', 'mch_name'],
 *  ],
 * 'category' => [
 *      'fields' => ['id', 'name', 'code'],
 *      'where' => [
 *          [
 *              'status',
 *              '=',
 *              1
 *          ],
 *          [
 *              'code',
 *              '=',
 *              'index-1'
 *          ],
 *      ],
 *   ]
 * ];
 *
 * {
 *  "fields": "id,title,code,mch_id",
 *  "includes": "merchant:id,mch_name;category:id,name,code:status=1",
 *  "with_counts": "category;merchant"
 *  }
 *
 * Class BaseApiController
 */
abstract class BaseApiController extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    use JsonResponseTrait;

    abstract public function getModel() :  \Illuminate\Database\Eloquent\Builder;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    public $operators = [
        '<=>',
        '<=',
        '>=',
        '<>',
        '!=',
        '=',
        '<',
        '>',
        'like binary',
        'not like',
        'ilike',
        '&',
        '|',
        '^',
        '<<',
        '>>',
        'rlike',
        'not ilike',
        'like',
    ];

    protected $allowApi   = true;
    protected $idName     = 'id';
    protected $fields     = ['*'];
    protected $includes   = [];
    protected $withCounts = [];
    protected $pageSize   = 10;
    protected $all        = [
        'fields' => ['*'],
        'where'  => null,
    ];

    protected $fillable      = null;
    protected $guarded       = null;
    protected $rules         = [];
    protected $messages      = [];
    protected $attributes    = [];
    protected $filters       = [];
    protected $isGroupSearch = false;
    protected $isUser        = false;
    protected $sortOrder     = [];

    private function getFileds()
    {
        if ($this->allowApi) {
            $this->fields = request('fields', $this->fields);
        }

        if (! is_array($this->fields)) {
            $this->fields = explode(',', $this->fields);
        }

        return $this->fields;
    }

    private function getPageSize()
    {
        return request('pageSize', $this->pageSize);
    }

    private function getSearch()
    {
        return request('search', []);
    }

    private function getSortOrder()
    {
        return request('sortOrder', $this->sortOrder);
    }

    private function getIncludes()
    {
        if ($this->allowApi) {
            $includes = request('includes', $this->includes);
        } else {
            $includes = $this->includes;
        }

        if (! is_array($includes)) {
            $includes  = explode(';', $includes);
            $_includes = [];
            if (! empty($includes)) {
                foreach ($includes as $key => $include) {
                    if (empty($include)) {
                        continue;
                    }

                    $_includes[] = $this->parseStringWhere($include, true);
                }
            }

            return $_includes;
        } else {
            $_includes = [];
            foreach ($includes as $key => $include) {
                $_includes[$key] = static function ($q) use ($include) {
                    if (isset($include['fields'])) {
                        $q->select($include['fields']);
                    }

                    if (isset($include['where'])) {
                        foreach ($include['where'] as $k => $val) {
                            if (is_array($val)) {
                                if (count($val) == 3) {
                                    $q->where($val[0], $val[1]);
                                } else if (count($val) == 2) {
                                    $q->where($val[0], $val[1], $val[2]);
                                } else {
                                    $q->whereRow($val[0]);
                                }
                            } else {
                                $q->whereRow($val);
                            }
                        }
                    }
                };
            }
        }

        return $_includes;
    }

    private function getWithCounts()
    {
        if ($this->allowApi) {
            $withCounts = request('with_counts', $this->withCounts);
        } else {
            $withCounts = $this->withCounts;
        }

        if (! is_array($withCounts)) {
            $withCounts  = explode(';', $withCounts);
            $_withCounts = [];

            foreach ($withCounts as $key => $val) {
                if (empty($val)) {
                    continue;
                }

                $_withCounts[] = $this->parseStringWhere($val, false);
            }
            return $_withCounts;
        } else {
            $_withCounts = [];
            foreach ($withCounts as $key => $withCount) {
                $_withCounts[$key] = static function ($q) use ($withCount) {
                    if (! isset($withCount['where'])) {
                        return;
                    }

                    foreach ($withCount['where'] as $k => $val) {
                        if (is_array($val)) {
                            if (count($val) == 3) {
                                $q->where($val[0], $val[1]);
                            } else if (count($val) == 2) {
                                $q->where($val[0], $val[1], $val[2]);
                            } else {
                                $q->whereRow($val[0]);
                            }
                        } else {
                            $q->selectRow($val);
                        }
                    }
                };
            }
        }

        return $_withCounts;
    }

    private function parseStringWhere($val, $inField = false)
    {
        $val = explode(':', $val);
        if (! is_array($val)) {
            return false;
        }

        $ret = [];
        if (isset($val[1])) {
            $ret[$val[0]] = function ($q) use ($val, $inField) {
                $fields = null;
                $where  = null;
                if ($inField === true) {
                    if (isset($val[1])) {
                        $fields = explode(',', $val[1]);
                    } else {
                        $fields = ['*'];
                    }

                    if (isset($val[2])) {
                        $where = explode(',', $val[2]);
                    }
                } else {
                    if (isset($val[1])) {
                        $where = explode(',', $val[1]);
                    }
                }

                if ($inField == true && $fields) {
                    $q->select($fields);
                }

                if (! $where) {
                    return;
                }

                foreach ($where as $key2 => $val2) {
                    foreach ($this->operators as $operator) {
                        $val2 = explode($operator, $val2);
                        if (count($val2) == 2) {
                            $q->where($val2[0], $operator, $val2[1]);
                            continue 2;
                        }
                    }
                }
            };
        } else {
            $ret = $val[0];
        }

        return $ret;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search     = $this->getSearch();
        $sort       = $this->getSortOrder();
        $page_size  = $this->getPageSize();
        $fields     = $this->getFileds();
        $includes   = $this->getIncludes();
        $withCounts = $this->getWithCounts();

        $model = $this->getModel();

        if ($this->isUser) {
            $model->where('user_id', auth()->user()->id);
        }

        if ($includes) {
            $model->with($includes);
        }

        if ($fields) {
            $model->select($fields);
        }

        if ($withCounts) {
            $model->withCount($withCounts);
        }

        if ($this->isGroupSearch) {
            $model->groupSearch($search);
        } else {
            $model->search($search);
        }

        $paginate = $model->sortOrder($sort)->paginate($page_size);

        return $this->success('messages.success', [
            'items'       => $paginate->items(),
            'total'       => $paginate->total(),
            'currentPage' => $paginate->currentPage(),
            'lastPage'    => $paginate->lastPage(),
        ]);
    }

    private function onlyInput()
    {
        if (! empty($this->fillable())) {
            $data = request()->only($this->fillable());
        } elseif (! empty($this->guarded())) {
            $data = request()->except($this->guarded());
        } else {
            $data = request()->input();
        }

        $this->addCustomFilters();

        $sanitizer = app('sanitizer')->make($data, $this->filters());
        $data      = $sanitizer->sanitize();

        if (! empty($this->rules()) && (! is_array(request()->input($this->idName)) && count($data) == 2)) {
            $validator = Validator::make($data, $this->rules(), $this->messages(), $this->attributes());

            if ($validator->fails()) {
                response()->json(
                    [
                        'code'    => HttpResponseCode::ERROR,
                        'message' => $validator->errors()->first(),
                    ],
                    200
                )->throwResponse();
            }
        }

        return $sanitizer->sanitize();
    }

    public function fillable()
    {
        return $this->fillable;
    }

    public function guarded()
    {
        return $this->guarded;
    }

    /**
     *  Add custom fields to the Sanitizer
     *
     *  @return void
     */
    public function addCustomFilters()
    {
        foreach ($this->customFilters() as $name => $filter) {
            app('sanitizer')->extend($name, $filter);
        }
    }

    public function customFilters()
    {
        return [];
    }

    /**
     *  Filters to be applied to the input.
     *
     *  @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        $id = $request->input($this->idName);

        if (empty($id)) {
            return $this->error('messages.select_data');
        }

        $fields     = $this->getFileds();
        $includes   = $this->getIncludes();
        $withCounts = $this->getWithCounts();

        $model = $this->getModel();

        if ($this->isUser === true) {
            $model->where('user_id', auth()->user()->id);
        }

        if ($includes) {
            $model->with($includes);
        }

        if ($fields) {
            $model->select($fields);
        }

        if ($withCounts) {
            $model->withCount($withCounts);
        }

        $data = $model->where($this->idName, $id)->firstOrError('messages.no_data');

        return $this->success('messages.success', $data);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = $this->onlyInput();

        $model = $this->getModel();

        if ($this->isUser === true) {
            $data['user_id'] = auth()->user()->id;
        }
        if ($this->isUser === true) {
            $data['user_id'] = auth()->user()->id;
        }

        if ($res = $model->create($data)) {
            return $this->success('messages.success', $res);
        }

        return $this->error('messages.error');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $data = $this->onlyInput();

        $id = array_get($data, $this->idName);
        if (empty($id)) {
            return $this->error('messages.select_data');
        }

        unset($data[$this->idName]);

        $model = $this->getModel();

        if (is_array($id)) {
            $query = $model->whereIn($this->idName, $id);

            if ($this->isUser === true) {
                $query->where('user_id', auth()->user()->id);
            }

            if ($query->update($data)) {
                return $this->success('messages.success');
            }
        } else {
            $model = $model->where($this->idName, $id)->firstOrError('messages.no_data');

            if ($this->isUser === true) {
                if ($model->user_id != auth()->user()->id) {
                    return $this->error('messages.error');
                }
            }

            if ($res = $model->fill($data)->save()) {
                $data[$this->idName] = $id;

                return $this->success('messages.success', $data);
            }
        }

        return $this->error('messages.error');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $id = $request->input($this->idName);
        if (is_array($id)) {
            $model = $this->getModel();

            if ($this->isUser === true) {
                $model->where('user_id', auth()->user()->id);
            }

            if ($model->whereIn($this->idName, $id)->delete()) {
                return $this->success('messages.success');
            }
        } else {
            $model = $this->getModel()->where($this->idName, $id)->firstOrError('messages.no_data');
            if ($this->isUser === true) {
                if ($model->user_id != auth()->user()->id) {
                    return $this->error('messages.error');
                }
            }

            if ($model->delete()) {
                return $this->success('messages.success');
            }
        }

        return $this->error('messages.error');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(Request $request)
    {
        $id = $request->input($this->idName);

        $model = $this->getModel();

        if ($this->isUser === true) {
            $model->where('user_id', auth()->user()->id);
        }

        if ($model->whereIn($this->idName, $id)->forceDelete()) {
            return $this->success('message.success');
        }

        return $this->error('messages.error');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function recyclebin(Request $request)
    {
        $search     = $this->getSearch();
        $sort       = $this->getSortOrder();
        $page_size  = $this->getPageSize();
        $fields     = $this->getFileds();
        $includes   = $this->getIncludes();
        $withCounts = $this->getWithCounts();

        $model = $this->getModel();

        if ($this->isUser) {
            $model->where('user_id', auth()->user()->id);
        }

        if ($includes) {
            $model->with($includes);
        }

        if ($fields) {
            $model->select($fields);
        }

        if ($withCounts) {
            $model->withCount($withCounts);
        }

        if ($this->isGroupSearch) {
            $model->groupSearch($search);
        } else {
            $model->search($search);
        }

        $paginate = $model->onlyTrashed()->sortOrder($sort)->paginate($page_size);

        return $this->success('messages.success', [
            'items'       => $paginate->items(),
            'total'       => $paginate->total(),
            'currentPage' => $paginate->currentPage(),
            'lastPage'    => $paginate->lastPage(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request)
    {
        $id = $request->input($this->idName);

        $model = $this->getModel();

        if ($this->isUser === true) {
            $model->where('user_id', auth()->user()->id);
        }

        if ($model->whereIn($this->idName, $id)->restore()) {
            return $this->success('message.success');
        }

        return $this->error('messages.error');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $model = $this->getModel();

        if ($this->isUser === true) {
            $model->where('user_id', auth()->user()->id);
        }

        $model->select(array_get($this->all, 'fields'));
        $where = array_get($this->all, 'where');
        if (! empty($where)) {
            foreach ($where as $k => $val) {
                if (is_array($val)) {
                    if (count($val) == 3) {
                        $model->select($val[0], $val[1], $val[2]);
                    } else if (count($val) == 2) {
                        $model->select($val[0], $val[1]);
                    } else {
                        $model->selectRow($val[0]);
                    }
                } else {
                    $model->selectRow($val);
                }
            }
        }

        $fields     = $this->getFileds();
        $includes   = $this->getIncludes();
        $withCounts = $this->getWithCounts();

        if ($includes) {
            $model->with($includes);
        }

        if ($fields) {
            $model->select($fields);
        }

        if ($withCounts) {
            $model->withCount($withCounts);
        }

        $items = $model->search($request->input('search', []))->get();

        return $this->success('messages.success', [
            'items' => $items,
        ]);
    }
}
