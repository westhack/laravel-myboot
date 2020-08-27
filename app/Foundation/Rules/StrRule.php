<?php

namespace App\Foundation\Rules;

class StrRule
{
    public function minLength($attribute, $value, $parameters, $validator)
    {
        if ($value == '') {
            return true;
        }

        return mb_strlen($value) >= (int)$parameters[0];
    }

    public function maxLength($attribute, $value, $parameters, $validator)
    {
        if ($value == '') {
            return true;
        }

        return mb_strlen($value) <= (int)$parameters[0];
    }

    public function phone($attribute, $value, $parameters, $validator)
    {
        if ($value == '') {
            return true;
        }

        preg_match("/^1[34578][0-9]{9}$/", $value, $match);

        return isset($match[0]);
    }

    public function replacer($message, $attribute, $rule, $parameters)
    {
        return str_replace('{0}', $parameters[0], $message);
    }
}
