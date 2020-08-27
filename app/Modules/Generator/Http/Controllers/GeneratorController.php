<?php

namespace App\Modules\Generator\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Yaml\Yaml;

class GeneratorController extends Controller
{

    public function model(Request $request)
    {
        \View::addExtension('html', 'php');

        $module            = $request->input('module');
        $table             = $request->input('table');
        $className         = $request->input('className');
        $isFillable        = $request->input('isFillable');
        $attributes        = $request->input('modelAttributes');
        $hasMany           = $request->input('modelHasManyRelationships');
        $hasManyForeignKey = $request->input('modelHasManyRelationshipsForeignKey');
        $hasManyLocalKey   = $request->input('modelHasManyRelationshipsLocalKey');
        $hasOne            = $request->input('modelHasOneRelationships');
        $hasOneForeignKey  = $request->input('modelHasOneRelationshipsForeignKey');
        $hasOneLocalKey    = $request->input('modelHasOneRelationshipsLocalKey');
        $hiddens           = $request->input('modelHiddenAttributes');
        $isSoftDelete      = $request->input('isSoftDelete');
        $isLog             = $request->input('isLog');


        $className = ucwords(camel_case($className));

        $m = Module::find($module);

        $moduleName = $m->getName();
        $modulePath = $m->getPath();
        $modelPath  = $modulePath . '/Models/' . $className . '.php';

        $relations = [];

        if (!empty($hasOne)) {
            foreach ($hasOne as $item) {
                $name = lcfirst($this->getClassName($item));
                if ($hasOneForeignKey[$item] && $hasOneLocalKey[$item]) {
                    $relations[$name] = '$this->hasOne(\'' . $item . '\', \'' . $hasOneForeignKey[$item] . '\', \'' . $hasOneLocalKey[$item] . '\')';
                } else  {
                    $relations[$name] = '$this->hasOne(\'' . $item . '\')';
                }
            }
        }
        if (!empty($hasMany)) {
            foreach ($hasMany as $item) {
                $name = lcfirst($this->getClassName($item));
                if ($hasManyForeignKey[$item] && $hasManyLocalKey[$item]) {
                    $relations[$name] = '$this->hasMany(\'' . $item . '\', \'' . $hasManyForeignKey[$item] . '\', \'' . $hasManyLocalKey[$item] . '\')';
                } else  {
                    $relations[$name] = '$this->hasMany(\'' . $item . '\')';
                }
            }
        }

        $sortOrder = [];
        $appends = [];

        $model = view()->file(module_path('generator', 'Resources/views/model.blade.php'), [
            'moduleName'     => $moduleName,
            'modelClassName' => ucwords($className),
            'table'          => $table,
            'sortOrder'      => $sortOrder,
            'attributes'     => $attributes,
            'hiddens'        => $hiddens,
            'isFillable'     => $isFillable,
            'appends'        => $appends,
            'relations'      => $relations,
            'isSoftDelete'   => $isSoftDelete,
            'isLog'          => $isLog,
        ])->render();

        if ($request->input('save') == true) {
            try {
                if (is_file($modelPath) && $request->input('force') == false) {
                    return $this->error('模型已存在');
                }

                if (file_put_contents($modelPath, $model)) {
                    return $this->success('模型保存成功', [
                        'modelHtml' => $model,
                        'modelPath' => $modelPath,
                    ]);
                } else {
                    return $this->error('模型保存失败');
                }
            } catch (\Throwable $exception) {
                return $this->error($exception->getMessage());
            }
        }

        return $this->success('ok', [
            'modelHtml' => $model,
        ]);
    }

    public function controller(Request $request)
    {
        \View::addExtension('html', 'php');

        $module                     = $request->input('module');
        $model                      = $request->input('model');
        $controllerName             = $request->input('controllerName');
        $hasManyRelationships       = $request->input('controllerHasManyRelationships');
        $hasManyRelationshipsFields = $request->input('controllerHasManyRelationshipsFields');
        $hasOneRelationships        = $request->input('controllerHasOneRelationships');
        $hasOneRelationshipsFields  = $request->input('controllerHasOneRelationshipsFields');
        $formDataSource             = $request->input('formDataSource');
        $formLabel                  = $request->input('formLabel');
        $formMethod                 = $request->input('formMethod');
        $formRule                   = $request->input('formRule');
        $formType                   = $request->input('formType');
        $pageSize                   = $request->input('pageSize', 10);
        $idName                     = $request->input('idName', 'id');
        $isUser                     = $request->input('isUser', false);

        $m = Module::find($module);

        $moduleName = $m->getName();
        $modulePath = $m->getPath();

        $controllerPath = $modulePath . '/Http/Controllers';
        $viewPath       = $modulePath . '/Resources/views';
        $routePath      = $modulePath . '/Routes/api.php';

        $prefix = '/'.lcfirst($moduleName);
        $allowApi = false;
        $fillable = null;
        $messages = [];
        $isGroupSearch = true;
        $isMch = false;
        $attributes = [];
        $sortOrder = [];
        $filters = [];
        $fields = [];
        $includes = [];
        $withCounts = [];
        $rules = [];

        $defaultFormData = [];
        $defaultSearchFormData = [];
        $columns = [
            [
                'type' => 'serial',
                'title' => '#',
                'width' => '30px',
                'align' => 'center',
                'dataIndex' => $idName
            ]
        ];

        $required = [
            'required' => true,
            'message' => '不能为空'
        ];

        $enums = [];

        if (!empty($formMethod)) {
            foreach ($formMethod as $key => $val) {
                if (in_array('list', $val)) {
                    if (stripos($key, '.') === false) {
                        $fields[] = $key;
                    } else {
                        $arr = explode('.', $key);
                        $includes[$arr[0]]['fields'][] = $arr[1];
                    }

                    if ($key != $idName) {
                        $type = 'text';
                        $data = [];
                        if ($formType[$key] == 'image') {
                            $type = 'image';
                        } else if ($formType[$key] == 'select') {
                            $type = 'select';
                            $data = $formDataSource[$key] ? $this->getData($formDataSource[$key], $type) : [];

                            if (is_array($data)) {
                                $enums[$key] = [
                                    'name' => $key,
                                    'label' => $formLabel[$key],
                                    'data' => $data
                                ];
                            }
                        }

                        $columns[] = [
                            'type'      => $type,
                            'title'     => $formLabel[$key],
                            'align'     => 'center',
                            'data'      => $data,
                            'dataIndex' => $key,
                            'sorter'    => in_array('sortOrder', $val) ? true : false,
                        ];
                    }
                }

                if (in_array('search', $val)) {
                    $name = $key;
                    if (stripos($key, '.') === false) {
                        $name = lcfirst($this->getClassName($model)) . '.' . $key;
                    }

                    $data = $formDataSource[$key] ? $this->getData($formDataSource[$key], $formType[$key]) : [];

                    $_t = [
                        'name'  => $name,
                        'label' => $formLabel[$key],
                        'type'  => $formType[$key],
                        'value' => '',
                    ];

                    if (is_string($data)) {
                        $_t['search'] = $data;
                    } else {
                        $_t['options'] = $data;
                    }

                    $defaultSearchFormData[] = $_t;
                }

                if (in_array('create', $val)) {
                    $data = $formDataSource[$key] ? $this->getData($formDataSource[$key], $formType[$key]) : [];

                    $vueRules = [];
                    if (isset($formRule[$key]) && in_array('required', $formRule[$key])) {
                        $vueRules[] = $required;
                        $rules[$key] = 'required';
                    }

                    $_t = [
                        'name'  => $key,
                        'label' => $formLabel[$key],
                        'type'  => $formType[$key],
                        'value' => '',
                        'rules' => $vueRules,
                    ];

                    if (is_string($data)) {
                        $_t['search'] = $data;
                    } else {
                        $_t['options'] = $data;
                    }

                    $defaultFormData[] = $_t;
                }
            }
        }

        if (!empty($hasManyRelationshipsFields)) {
            foreach ($hasManyRelationshipsFields as $key => $val) {
                $arr = explode('|', $key);
                $includes[$arr[0]]['fields'] = $val;
            }
        }

        if (!empty($formLabel)) {
            foreach ($formLabel as $key => $val) {
                $attributes[$key] = $val;
            }
        }

        if (!empty($formRule)) {
            foreach ($formRule as $key => $val) {
                $rules[$key] = implode('|', $val);
            }
        }

        $class = str_ireplace('Controller', '', $controllerName);

        $apiList = [
            'list'   => $prefix .'/' . lcfirst($class) . '/list',
            'create' => $prefix .'/' . lcfirst($class) . '/create',
            'update' => $prefix .'/' . lcfirst($class) . '/update',
            'delete' => $prefix .'/' . lcfirst($class) . '/delete',
            'detail' => $prefix .'/' . lcfirst($class) . '/detail',
            'all'    => $prefix .'/' . lcfirst($class) . '/all',
        ];

        $controller = view()->file(module_path('generator', 'Resources/views/controller.blade.php'), [
            'moduleName'          => $module,
            'controllerClassName' => $controllerName,
            'modelClassName'      => $model,
            'allowApi'            => $allowApi,
            'fields'              => $fields,
            'includes'            => $includes,
            'rules'               => $rules,
            'filters'             => $filters,
            'attributes'          => $attributes,
            'pageSize'            => $pageSize,
            'sortOrder'           => $sortOrder,
            'fillable'            => $fillable,
            'messages'            => $messages,
            'withCounts'          => $withCounts,
            'isGroupSearch'       => $isGroupSearch,
            'isUser'              => $isUser,
            'isMch'               => $isMch,
            'idName'              => $idName,
            'apiList'             => $apiList
        ])->render();

        $view = view()->file(module_path('generator', 'Resources/views/view.blade.php'), [
            'moduleName' => $moduleName,
            'viewClassName' => $class,
            'idName' => $idName,
            'columns' => $columns,
            'apiList' => $apiList,
            'defaultFormData' => $defaultFormData,
            'defaultSearchFormData' => $defaultSearchFormData,
            'enums' => $enums,
        ])->render();

        $route = view()->file(module_path('generator', 'Resources/views/route.blade.php'), [
            'moduleName' => $moduleName,
            'controllerClassName' => $controllerName,
            'apiList' => $apiList,
        ])->render();

        if ($request->input('save') == true) {
            try {
                if (is_file($controllerPath) && $request->input('force') == false) {
                    return $this->error('控制器已存在');
                }

                if (is_file($viewPath) && $request->input('force') == false) {
                    return $this->error('Vue 视图已存在');
                }

                if (file_put_contents($controllerPath, $controller) && file_put_contents($controllerPath, $viewPath)) {

                    file_put_contents($routePath, $route, FILE_APPEND);

                    return $this->success('保存成功', [
                        'controllerHtml' => $controller,
                        'viewHtml' => $view,
                        'controllerPath' => $controllerPath,
                        'viewPath' => $viewPath,
                        'routePath' => $routePath,
                    ]);
                } else {
                    unlink($controllerPath);
                    unlink($viewPath);

                    return $this->error('保存失败');
                }
            } catch (\Throwable $exception) {
                return $this->error($exception->getMessage());
            }
        }

        return $this->success('ok', [
            'controllerHtml' => $controller,
            'viewHtml' => $view,
        ]);
    }

    private function getSchemas()
    {
//        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
//
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTables();

        $schemas = [];
        foreach ($tables as $t) {
            $schemas['tables'][] = [
                'label' => $t->getComment() ? $t->getName() . '(' . $t->getComment() . ')' : $t->getName(),
                'value' => $t->getName(),
            ];
            //var_dump($t->getColumns());

            $table = $t->getName();

            $columns = $t->getColumns();
            foreach ($columns as $column) {
                $schemas['columns'][$table][] = [
                    'label' => $column->getComment() ? $column->getName() . '(' . $column->getComment() . ')' : $column->getName(),
                    'value' => $column->getName(),
                ];

                //$schemas['columns'][$table][$column->getName()] = $column->toArray();
                //$schemas['columns'][$table][$column->getName()]['type'] = $column->getType()->getName();
            }
        }

        return $schemas;
    }

    private function getModels($modules)
    {
        $models = [];
        $modelHasTable = [];

        $filesystem = new Filesystem();

        $root_path = base_path();
        foreach ($modules as $key => $module) {
            $path = $module['path'] . '/Models';
            if (!is_dir($path)) {
                continue;
            }
            $files = $filesystem->allFiles($path);
            foreach ($files as $file) {
                $filename = $file->getPathname();
                $filename = str_replace($root_path . '/app/', 'App/', $filename);
                $filename = str_replace('.php', '', $filename);
                $filename = str_replace('/', '\\', $filename);

                if (class_exists($filename)) {

                    try {
                        $t = env('DB_PRE') . with(new $filename())->getTable();
                        if ($t) {
                            $models[] = $filename;
                            $modelHasTable[$filename] = $t;
                        }
                    } catch (\Throwable $exception) {
                    }
                }
            }
        }

        return [
            'models' => $models,
            'modelHasTable' => $modelHasTable
        ];
    }

    private function getModules()
    {
        $ret = [];
        $modules = Module::collections()->toArray();
        foreach ($modules as $module) {
            $ret[] = [
                'label' => $module['description'] ? $module['description'] : $module['name'],
                'name'  => $module['name'],
                'path'  => $module['path'],
                'value' => $module['name'],
            ];
        }

        return $ret;
    }

    public function form()
    {
        //ini_set('memory_limit', 0);
        $modules = $this->getModules();
        $models = $this->getModels($modules);

        $modelHasTable = $models['modelHasTable'];
        $models = $models['models'];

        $schemas = $this->getSchemas();

        $model = [
            'module' => [
                'name' => 'module',
                'type' => 'select',
                'label' => '模块',
                'value' => '',
                'data' => $modules,
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
                'placeholder' => '选择模块',
                'showSearch' => true
            ],

            'table' => [
                'name' => 'table',
                'type' => 'select',
                'label' => '数据表',
                'value' => '',
                'data' => $schemas['tables'],
                'placeholder' => '选择数据表',
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
                'showSearch' => true
            ],

            'className' => [
                'name' => 'className',
                'type' => 'input',
                'label' => '模型名称',
                'value' => '',
                'placeholder' => '模型名称',
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
            ],

            'isSoftDelete' => [
                'name' => 'isSoftDelete',
                'type' => 'switch',
                'label' => '是否软删除',
                'value' => false,
            ],

            'isLog' => [
                'name' => 'isLog',
                'type' => 'switch',
                'label' => '是否记录变更日志',
                'value' => false,
            ],

            'isFillable' => [
                'name' => 'isFillable',
                'type' => 'switch',
                'label' => '是否被批量赋值',
                'value' => false,
            ],

            'modelAttributes' => [
                'name' => 'modelAttributes',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '不可被批量赋值的属性',
                'value' => [],
                'data' => [],
                'showSearch' => true
            ],

            'modelHiddenAttributes' => [
                'name' => 'modelHiddenAttributes',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '模型隐藏字段',
                'value' => [],
                'data' => [],
                'showSearch' => true
            ],

            'modelHasOneRelationships' => [
                'name' => 'modelHasOneRelationships',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '一对一模型关联',
                'value' => [],
                'data' => $models,
                'showSearch' => true
            ],

            'modelHasManyRelationships' => [
                'name' => 'modelHasManyRelationships',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '一对多模型关联',
                'value' => [],
                'data' => $models,
                'showSearch' => true
            ],

        ];

        $controller = [
            'module' => [
                'name' => 'module',
                'type' => 'select',
                'label' => '模块',
                'value' => '',
                'data' => $modules,
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
                'placeholder' => '选择模块',
                'showSearch' => true
            ],

            'model' => [
                'name'  => 'model',
                'type'  => 'select',
                'label' => '选择模型',
                'value' => '',
                'data'  => $models,
                'showSearch' => true,
                'placeholder' => '选择模型',
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
            ],

            'idName' => [
                'name'  => 'idName',
                'type'  => 'select',
                'label' => '模型主键',
                'value' => 'id',
                'data'  => [],
                'showSearch' => true,
                'placeholder' => '选择模型主键',
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
            ],

            'pageSize' => [
                'name'  => 'pageSize',
                'type'  => 'input-number',
                'label' => '分页大小',
                'value' => 10,
                'placeholder' => '分页大小',
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
            ],

            'controllerName' => [
                'name'  => 'controllerName',
                'type'  => 'input',
                'label' => '控制器名称',
                'value' => '',
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
                'placeholder' => '输入控制器名称'
            ],

            'isUser' => [
                'name' => 'isUser',
                'type' => 'switch',
                'label' => '是否判断用户',
                'value' => false,
            ],

            'controllerHasOneRelationships' => [
                'name' => 'controllerHasOneRelationships',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '一对一模型关联',
                'value' => [],
                'data' => [],
                'placeholder' => '选择要展示的关联模型',
                'showSearch' => true
            ],

            'controllerHasManyRelationships' => [
                'name' => 'controllerHasManyRelationships',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '一对多模型关联',
                'value' => [],
                'data' => [],
                'placeholder' => '选择要展示的关联模型',
                'showSearch' => true
            ],

//            'apiList' => [
//                'name'  => 'apiList',
//                'type'  => 'input',
//                'label' => '列表接口地址',
//                'value' => '',
//                'rules' => [
//                    [
//                        'required' => true,
//                        'message' => '不能为空'
//                    ]
//                ],
//            ],
//            'apiCreate' => [
//                'name'  => 'apiCreate',
//                'type'  => 'input',
//                'label' => '创建接口地址',
//                'value' => '',
//                'rules' => [
//                    [
//                        'required' => true,
//                        'message' => '不能为空'
//                    ]
//                ],
//            ],
//            'apiUpdate' => [
//                'name'  => 'apiCreate',
//                'type'  => 'input',
//                'label' => '更新接口地址',
//                'value' => '',
//                'rules' => [
//                    [
//                        'required' => true,
//                        'message' => '不能为空'
//                    ]
//                ],
//            ],
//            'apiDelete' => [
//                'name'  => 'apiCreate',
//                'type'  => 'input',
//                'label' => '删除接口地址',
//                'value' => '',
//                'rules' => [
//                    [
//                        'required' => true,
//                        'message' => '不能为空'
//                    ]
//                ],
//            ],
//            'apiDetail' => [
//                'name'  => 'apiDetail',
//                'type'  => 'input',
//                'label' => '详细接口地址',
//                'value' => '',
//                'rules' => [
//                    [
//                        'required' => true,
//                        'message' => '不能为空'
//                    ]
//                ],
//            ],
//            'apiAll' => [
//                'name'  => 'apiAll',
//                'type'  => 'input',
//                'label' => '全部列表接口地址',
//                'value' => '',
//                'rules' => [
//                    [
//                        'required' => true,
//                        'message' => '不能为空'
//                    ]
//                ],
//            ],
        ];

        $view = [
            'module' => [
                'name' => 'module',
                'type' => 'select',
                'label' => '模块',
                'value' => '',
                'data' => $modules,
                'rules' => [
                    [
                        'required' => true,
                        'message' => '不能为空'
                    ]
                ],
                'placeholder' => '选择模块'
            ],

            'modelAttributes' => [
                'name' => 'modelAttributes',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '显示字段',
                'value' => [],
                'data' => []
            ],

            'modelHasOneRelationships' => [
                'name' => 'modelHasOneRelationships',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '一对一模型关联',
                'value' => [],
                'data' => []
            ],

            'modelHasManyRelationships' => [
                'name' => 'modelHasManyRelationships',
                'type' => 'select',
                'maxTagCount' => 100,
                'multiple' => true,
                'label' => '一对多模型关联',
                'value' => [],
                'data' => []
            ]
        ];

        return $this->success('success', [
            'model'         => $model,
            'controller'    => $controller,
            'view'          => $view,
            'table'         => $schemas['tables'],
            'tableColumns'  => $schemas['columns'],
            'modelHasTable' => $modelHasTable,
            'modules'       => $modules,
        ]);
    }

    public function getRelations()
    {
        $model = request('model');
        if (empty($model)) {
            return $this->error();
        }

        $modules = Module::collections()->toArray();
        $models = $this->getModels($modules);

        $modelHasTable = $models['modelHasTable'];
        $models = $models['models'];

        if (!in_array($model, $models) || !$modelHasTable[$model]) {
            return $this->error();
        }

        $model_arr = explode("\\", $model);
        unset($model_arr[count($model_arr) - 1]);
        $prefix = implode("\\", $model_arr);

        $root_path = base_path();
        $file = str_replace('\\', '/', lcfirst(ltrim($model, '\\')));
        $str = file_get_contents( $root_path . '/' . $file . '.php');

//        preg_match_all("/public\s*function\s*[^\w]([\w]+)\s*\(\s*[\w]*\s*\)\s*{\s*(.*)\s*}/", $str, $matches);
        preg_match_all("/public\s*function\s*[^\w]([\w]+)\s*\(\s*[\w]*\s*\)\s*{\s*(.*)\s*/", $str, $matches);
        preg_match_all("/use\s*(.*);/", $str, $matches1);
        $class = [];
        if (isset($matches1[1])) {
            foreach ($matches1[1] as $key => $val) {
                $arr = explode('\\', $val);
                if (is_array($arr)) {
                    $class[$arr[count($arr) - 1] . '::class'] = $val;
                }
            }
        }

        $ret = [];

        $ret['hasOne'] = [];
        $ret['hasMany'] = [];
        foreach ($matches[1] as $key => $val) {

            $matches[2][$key] = str_replace('"', "'", $matches[2][$key]);
            $matches[2][$key] = preg_replace("/\s*/", '', $matches[2][$key]);
            preg_match("/hasOne\((.*),'\w+','\w+'/", $matches[2][$key], $matches2);

            if ($matches2 && isset($matches2[1])) {

                $matches2[1] = str_replace("'", '', $matches2[1]);
                if (stripos($matches2[1], '::class') !== false) {
                    if (isset($class[$matches2[1]])) {
                        $ret['hasOne'][] = [
                            'label' => $val,
                            'value' => $val . '|' . $class[$matches2[1]],
                        ];
                    } else {
                        $matches2[1] = str_replace('::class', '', $matches2[1]);
                        $ret['hasOne'][] = [
                            'label' => $val,
                            'value' => $val . '|' . $prefix . '\\' . $matches2[1],
                        ];
                    }

                } else {
                    $ret['hasOne'][] = [
                        'label' => $val,
                        'value' => $val . '|' . str_replace("'", '', $matches2[1]),
                    ];
                }

            }

            preg_match("/hasMany\((.*),'\w+','\w+'/", $matches[2][$key], $matches3);
            if ($matches3 && isset($matches3[1])) {

                $matches3[1] = str_replace("'", '', $matches3[1]);
                if (stripos($matches3[1], '::class') !== false) {
                    if (isset($class[$matches3[1]])) {
                        $ret['hasMany'][] = [
                            'label' => $val,
                            'value' =>  $val . '|' .  $class[$matches3[1]],
                        ];

                    } else {
                        $matches3[1] = str_replace('::class', '', $matches3[1]);

                        $ret['hasMany'][] = [
                            'label' => $val,
                            'value' =>  $val . '|' .  $prefix . '\\' . $matches3[1],
                        ];
                    }

                } else {
                    $ret['hasMany'][] = [
                        'label' => $val,
                        'value' => $val . '|' . str_replace("'", '', $matches3[1]),
                    ];
                }

            }

        }

        return $this->success('success', $ret);
    }

    public function getTableColumns()
    {
        $model = request('model');
        if (empty($model)) {
            return $this->error();
        }

        $modules = Module::collections()->toArray();
        $models = $this->getModels($modules);

        $modelHasTable = $models['modelHasTable'];
        $models = $models['models'];

        if (!in_array($model, $models) || !$modelHasTable[$model]) {
            return $this->error();
        }

        $table = $modelHasTable[$model];

        $schemas = $this->getSchemas();
        if (!isset($schemas['columns'][$table])) {
            return $this->error();
        }

        return $this->success('success', [
            'tableColumns'  => $schemas['columns'][$table],
        ]);
    }

    private function getClassName($val)
    {
        return class_basename($val);
    }

    private function getData($val, $type = null)
    {
        $ret = json_decode($val, true);
        if (!json_last_error()) {
            return $ret;
        }

        $ret = Yaml::parse($val);
        if (is_array($ret)) {
            return $ret;
        }

        $ret = [];
        $arr = explode("\n", $val);
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                $arr2 = explode('=', $v);
                if (is_array($arr2)) {
                    $ret[] = [
                        'label' => $arr2['1'],
                        'value' => $arr2['0'],
                    ];
                }
            }

            return $ret;
        }

        return $val;
    }

}
