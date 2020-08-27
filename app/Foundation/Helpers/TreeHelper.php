<?php

namespace App\Foundation\Helpers;

class TreeHelper
{
    public static function toTree($data, $id_name = 'id', $parent_name = 'parent_id', $parent = null, $children = 'children', $sort_order = null)
    {
        $result = [];
        if ($data) {

            $data = ArrayHelper::index($data, $id_name);

            $result = static::_tree($data, $id_name, $parent_name, $parent, $children, $sort_order);
        }

        return $result;
    }

    private static function _tree(&$data, $id_name, $parent_name, $parent = null, $children = 'children', $sort_order = null)
    {
        $result = [];
        $order = [];
        foreach ($data as $key => $item) {
            if ($item[$parent_name] == $parent) {
                $item[$children] = static::_tree($data, $id_name, $parent_name, $key, $children, $sort_order);

                $result[] = $item;
                if ($sort_order) {
                    $order[] = $item[$sort_order];
                }
            }
        }
        if ($result !== [] && $order && $sort_order) {
            array_multisort($order, $result);
        }

        return $result;
    }
}
