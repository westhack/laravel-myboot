<?php

namespace App\Foundation\Model;

trait BatchUpdateTrait
{
    public static function getTableName()
    {
        return env('DB_PRE') . with(new static())->getTable();
    }

    public static function batchUpdate(array $update_arr, string $key)
    {
        $model = new static();
        $table_name = env('DB_PRE') . $model->getTable();

        if (! $table_name || ! $key || ! $update_arr) {
            return false;
        }

        $update_arr = self::_sanitizers($update_arr, $model);

        $update_keys       = array_keys($update_arr[0]);
        $update_keys_count = count($update_keys);

        for ($i = 0; $i < $update_keys_count; $i++) {
            $key_name = $update_keys[$i];
            if ($key === $key_name) {
                continue;
            }
            $when_{$key_name} = $key_name . ' = CASE';
        }

        $length    = count($update_arr);
        $index     = 0;
        $query_str = 'UPDATE ' . $table_name . ' SET ';
        $when_str  = '';
        $where_str = ' WHERE ' . $key . ' IN(';

        while ($index < $length) {
            $when_str   = " WHEN $key = '{$update_arr[$index][$key]}' THEN";
            $where_str .= "'{$update_arr[$index][$key]}',";
            for ($i = 0; $i < $update_keys_count; $i++) {
                $key_name = $update_keys[$i];
                if ($key === $key_name) {
                    continue;
                }
                $when_{$key_name} .= $when_str . " '{$update_arr[$index][$key_name]}'";
            }
            $index++;
        }

        for ($i = 0; $i < $update_keys_count; $i++) {
            $key_name = $update_keys[$i];
            if ($key === $key_name) {
                continue;
            }
            $when_{$key_name} .= ' ELSE ' . $key_name . ' END, ';
            $query_str        .= $when_{$key_name};
        }
        $query_str  = rtrim($query_str, ', ');
        $where_str  = rtrim($where_str, ',') . ')';
        $query_str .= $where_str;

        return \DB::update($query_str);
    }

    private static function _sanitizers(array $update_arr, $model)
    {
        foreach ($update_arr as $key => $item) {
            foreach ($item as $name => $value) {
                if (
                    $name != $model->getKeyName() &&
                    ((!empty($model->guarded) && in_array($name, $model->guarded))
                    || (!empty($model->fillable) && !in_array($name, $model->fillable)))
                ) {
                    unset($item[$name]);
                }
            }

            if (method_exists($model, 'sanitizer')) {
                $item = $model->sanitizer($item);
            }
            $update_arr[$key] = $item;
        }

        return $update_arr;
    }
}
