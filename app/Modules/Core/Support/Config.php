<?php

namespace App\Modules\Core\Support;

class Config
{
    public static function get($name, $default = null)
    {
        $configs = static::getConfigs();
        if (array_get($configs, $name)) {
            return array_get($configs, $name);
        }

        return $default;
    }

    public static function getConfigs()
    {
        return \App\Modules\Core\Models\Config::get()->pluck('value', 'name')->toArray();
    }
}
