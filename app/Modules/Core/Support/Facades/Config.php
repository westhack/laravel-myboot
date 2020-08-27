<?php

namespace App\Modules\Core\Support\Facades;

use App\Foundation\Helpers\ArrayHelper;
use Illuminate\Support\Facades\Config as BaseConfig;

class Config extends BaseConfig
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public static function write($config, array $newValues = [])
    {
        $all = self::get($config);
        $all = array_merge($all, $newValues);

        file_put_contents(config_path('params/' . $config . '.php'), "<?php \n  return ". var_export($all, true) .";");
    }

    public static function writeModuleAll()
    {
        $modules = ArrayHelper::index(\App\Modules\Core\Models\Config::whereNotNull('parent_name')->get(['value', 'name', 'module'])->toArray(), 'name', 'module');
        foreach ($modules as $key => $module) {
            foreach ($module as $name => $config) {
                $module[$name] = $config['value'];
            }
            file_put_contents(config_path('params/' . $key . '.php'), "<?php \n  return ". var_export($module, true) .";");
        }
    }
}
