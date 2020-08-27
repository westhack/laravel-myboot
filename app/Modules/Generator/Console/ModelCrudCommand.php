<?php

namespace App\Modules\Generator\Console;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelCrudCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'model';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为指定的模块创建一个新模型，包含CRUD';

    public function handle() : int
    {
        Stub::setBasePath('/');
        // Model
        $path = str_replace('\\', '/', $this->getModelDestinationFilePath());

        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $contents = $this->getModelTemplateContents();

        try {
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");

            return E_ERROR;
        }

        // Controller
        $path = str_replace('\\', '/', $this->getControllerDestinationFilePath());

        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $contents = $this->getControllerTemplateContents();

        try {
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");

            return E_ERROR;
        }

        // View
        $path = str_replace('\\', '/', $this->getViewsDestinationFilePath());
        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $contents = $this->getViewsTemplateContents();

        try {
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");

            return E_ERROR;
        }

        $path = str_replace('\\', '/', $this->getRouteDestinationFilePath());
        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $source = file_get_contents($path);
        $contents = $this->getRoutesTemplateContents();
        $source = str_replace("\r\n" . $contents, '', $source);
        $contents = $source . "\r\n" . $contents;

        file_put_contents($path, $contents);

        $this->info("Updated : {$path}");

        $this->handleOptionalMigrationOption();

        return 0;
    }

    /**
     * Create a proper migration name:
     * ProductDetail: product_details
     * Product: products
     * @return string
     */
    private function createMigrationName()
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->argument('model'), -1, PREG_SPLIT_NO_EMPTY);

        $string = '';
        foreach ($pieces as $i => $piece) {
            if ($i+1 < count($pieces)) {
                $string .= strtolower($piece) . '_';
            } else {
                $string .= Str::plural(strtolower($piece));
            }
        }

        return $string;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, '模型名称'],
            ['module', InputArgument::OPTIONAL, '模块名称.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, '强制覆盖', null],
            ['guarded', null, InputOption::VALUE_OPTIONAL, '不允许批量赋值', null],
            ['migration', 'm', InputOption::VALUE_NONE, '标记来创建关联的迁移', null],
        ];
    }

    /**
     * Create the migration file with the given model if migration flag was used
     */
    private function handleOptionalMigrationOption()
    {
        if ($this->option('migration') === true) {
            $migrationName = 'create_' . $this->createMigrationName() . '_table';
            $this->call('module:make-migration', ['name' => $migrationName, 'module' => $this->argument('module')]);
        }
    }

    /**
     * @return mixed
     */
    protected function getModelTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub(module_path('Generator') . '/Console/stub/model.stub', [
            'NAME'             => $this->getModelName(),
            'GUARDED'          => $this->getGuarded(),
            'NAMESPACE'        => $this->getModleDefaultNamespace($module),
            'CLASS'            => $this->getClass(),
            'LOWER_NAME'       => $module->getLowerName(),
            'MODULE'           => $this->getModuleName(),
            'STUDLY_NAME'      => $module->getStudlyName(),
            'MODULE_NAMESPACE' => $this->laravel['modules']->config('namespace'),
        ]))->render();
    }

    /**
     * @return string
     */
    protected function getControllerTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub(module_path('Generator') . '/Console/stub/controller.stub', [
            'MODULENAME'        => $module->getStudlyName(),
            'CONTROLLERNAME'    => $this->getControllerName(),
            'MODELNAME'         => $this->getModleDefaultNamespace($module) . '\\' .$this->getModelName(),
            'NAMESPACE'         => $module->getStudlyName(),
            'CLASS_NAMESPACE'   => $this->getControllerDefaultNamespace($module),
            'CLASS'             => $this->getControllerNameWithoutNamespace(),
            'LOWER_NAME'        => lcfirst($this->getModelName()),
            'LOWER_MODULE_NAME' => $module->getLowerName(),
            'MODULE'            => $this->getModuleName(),
            'NAME'              => $this->getModuleName(),
            'STUDLY_NAME'       => $module->getStudlyName(),
            'MODULE_NAMESPACE'  => $this->laravel['modules']->config('namespace'),
        ]))->render();
    }

    /**
     * @return string
     */
    protected function getViewsTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());
        $schemas = $this->getSchemas($module);

        return (new Stub(module_path('Generator') . '/Console/stub/views.stub', [
            'NAME'              => $this->getModelName(),
            'LOWER_NAME'        => lcfirst($this->getModelName()),
            'LOWER_MODULE_NAME' => $module->getLowerName(),
            'MODULE'            => $this->getModuleName(),
            'FORM'              => json_encode($schemas['form'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'COLUMNS'           => json_encode($schemas['columns'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'SEARCH'            => json_encode($schemas['search'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]))->render();
    }

    /**
     * @return string
     */
    protected function getRoutesTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub(module_path('Generator') . '/Console/stub/routes.stub', [
            'LOWER_NAME'        => ucfirst($this->getModelName()),
            'LOWER_MODULE_NAME' => $module->getLowerName(),
            'CLASS'             => $this->getControllerNameWithoutNamespace(),
        ]))->render();
    }

    /**
     * @return mixed
     */
    protected function getModelDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('model');

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . '.php';
    }

    /**
     * @return mixed
     */
    protected function getControllerDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $controllerPath = GenerateConfigReader::read('controller');

        return $path . $controllerPath->getPath() . '/' . $this->getControllerName() . '.php';
    }

    /**
     * @return mixed
     */
    protected function getViewsDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $controllerPath = GenerateConfigReader::read('views');

        return $path . $controllerPath->getPath() . '/' . $this->getViewsName() . '.vue';
    }

    /**
     * @return mixed
     */
    protected function getRouteDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $routePath = 'Routes';

        return $path . $routePath . '/api.php';
    }

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('model'));
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->argument('model'));

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return array|string
     */
    protected function getViewsName()
    {
        $views = Str::studly($this->argument('model'));

        return $views;
    }

    /**
     * @return array|string
     */
    private function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    /**
     * @return string
     */
    private function getGuarded()
    {
        $guarded = $this->option('guarded');

        if (!is_null($guarded)) {
            $arrays = explode(',', $guarded);

            return json_encode($arrays);
        }

        return '[]';
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getModleDefaultNamespace($module) : string
    {
        $_module = $this->laravel['modules'];

        $name = $_module->config('paths.generator.model.namespace') ?: $_module->config('paths.generator.model.path', 'Entities');

        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));

        $extra = str_replace('/', '\\', $extra);

        $namespace = $this->laravel['modules']->config('namespace');

        $namespace .= '\\' . $module->getStudlyName();

        $namespace .= '\\' . $name;

        $namespace .= '\\' . $extra;

        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');

    }

    public function getControllerDefaultNamespace($module) : string
    {
        $_module = $this->laravel['modules'];

        $name = $_module->config('paths.generator.controller.namespace') ?: $_module->config('paths.generator.controller.path', 'Http/Controllers');

        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));

        $extra = str_replace('/', '\\', $extra);

        $namespace = $this->laravel['modules']->config('namespace');

        $namespace .= '\\' . $module->getStudlyName();

        $namespace .= '\\' . $name;

        $namespace .= '\\' . $extra;

        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }

    public function getSchemas($module)
    {
        $columns = [
            [
                'title'       => '#',
                'scopedSlots' => [ 'customRender' => 'serial' ],
                'width'       => '30px',
                'align'       => 'center',
                'dataIndex'   => 'id',
            ],
        ];

        $form = [];
        $search = [];

        $model = $this->getModleDefaultNamespace($module) . '\\' .$this->getModelName();
        if (class_exists($model)) {
            $model = new $model();
            $t = $model->getTable();
            $listTableColumns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($t);
            foreach($listTableColumns as $column) {
                $label = $column->getComment() ? $column->getComment() : $column->getName();
                if ($column->getName() != 'id') {
                    $columns[] = [
                        'title'     => $label,
                        'dataIndex' => $column->getName(),
                        'align'     => 'center',
                    ];
                }

                $form[] = [
                    'name'  => $column->getName(),
                    'label' => $label,
                    'type'  => 'input',
                    'value' => '',
                    'options' => []
                ];
                $search[] = [
                    'name'  => $column->getName(),
                    'label' => $label,
                    'type'  => 'input',
                    'value' => '',
                    'options' => []
                ];
            }
        }

        $columns[] = [
            'title'       => '操作',
            'dataIndex'   => 'action',
            'align'       => 'center',
            'width'       => '150px',
            'fixed'       => 'right',
            'scopedSlots' => [ 'customRender' => 'action' ],
        ];

        return [
            'columns' => $columns,
            'form'    => $form,
            'search'  => $search,
        ];
    }
}
