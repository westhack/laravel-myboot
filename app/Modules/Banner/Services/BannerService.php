<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Banner\Services;

use App\Modules\Banner\Models\Banner;

class BannerService
{
    /**
     * @param string $code
     * @param string $mch_id
     * @param array  $columns
     *
     * @return Banner[]
     */
    public static function all(string $code, ?string $mch_id = null, $columns = ['title', 'code', 'jump', 'image'])
    {
        $query = Banner::active()->whereCode($code);

        if ($mch_id) {
            $query->whereMchId($mch_id);
        }

        return $query->orderBy('sort_order')->get($columns);
    }

    /**
     * @param string $code
     * @param string $mch_id
     * @param array  $columns
     *
     * @return Banner
     */
    public static function find(string $code, ?string $mch_id = null, $columns = ['title', 'code', 'jump', 'image'])
    {
        $query = Banner::active()->whereCode($code);

        if ($mch_id) {
            $query->whereMchId($mch_id);
        }

        return $query->orderBy('sort_order')->first($columns);
    }
}
