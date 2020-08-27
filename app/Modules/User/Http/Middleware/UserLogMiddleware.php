<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserLogMiddleware
{
    public function handle(Request $request, Closure $next, $log = '')
    {
        $response = $next($request);

        $url = $request->getRequestUri();

        activity()
            ->withProperties([
                'url'      => $url,
                'request'  => $request->input(),
                'response' => $response
            ])
            ->log($log);

        return $response;
    }
}
