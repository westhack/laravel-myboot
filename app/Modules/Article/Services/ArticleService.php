<?php

namespace App\Modules\Article\Services;

use App\Modules\Article\Models\Article;

class ArticleService
{
    /**
     * @param $category_id
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function get($category_id, $limit = 10)
    {
        return Article::where('category_id', $category_id)->where('status', 1)->limit($limit)->orderByDesc('id')->get();
    }
}
