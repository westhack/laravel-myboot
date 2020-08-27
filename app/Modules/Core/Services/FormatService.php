<?php

namespace App\Modules\Core\Services;

use Symfony\Component\Yaml\Yaml;

class FormatService
{

    public function normal($value)
    {
        $value = explode("\n", $value);
        foreach ($value as $key => $val) {
            $value[$key] = trim($val);
        }

        return $value;
    }

    public function json($value)
    {
        $_value = json_decode($value, true);
        $error  = json_last_error();

        if ($error === 0) {
            return $_value;
        }

        return $value;
    }

    public function yaml($value)
    {
        $_value = Yaml::parse($value);

        return $_value;
    }

}
