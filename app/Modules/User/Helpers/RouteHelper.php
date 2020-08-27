<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Helpers;

class RouteHelper
{
    public static function getRouteNames()
    {
        $app    = app();
        $routes = $app->routes->getRoutes();

        $routeNames = [];
        foreach ($routes as $k => $value) {
            $routeNames[$k]['name']    = array_get($value->action, 'as', '');
            $routeNames[$k]['route']   = $value->uri;
            $routeNames[$k]['action']  = $value->getActionName();
            $routeNames[$k]['methods'] = $value->methods;
        }

        return $routeNames;
    }
}
