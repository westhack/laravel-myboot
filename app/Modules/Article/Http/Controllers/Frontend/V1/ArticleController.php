<?php
/**
 * @link http://www.xinrennet.com/
 *
 * @copyright Copyright (c) 2020 Xinrennet Software LLC
 * @author  Yao <yao@xinrennet.com>
 */

namespace App\Modules\Article\Http\Controllers\Fraontend\V1;

use App\Http\Controllers\Controller;
use App\Modules\Article\Models\Article;

class ArticleController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/article/v1/list", tags = {"前端-资讯"}, summary="资讯列表",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="category_id", type="string",  description="分类ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200", description="成功",),
     *      @OA\Response(response="500", description="失败",),
     * )
     */
    public function index()
    {
        $page_size   = request('page_size');
        $category_id = request('category_id');

        $query = Article::query();

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        $page = $query->where('status', 1)
            ->orderByDesc('id')
            ->paginate($page_size);

        return $this->success('messages.success', [
            'current_age' => $page->currentPage(),
            'total'       => $page->total(),
            'items'       => $page->items(),
            'lastPage'   => $page->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/article/v1/detail", tags = {"前端-资讯"}, summary="资讯详细",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="id", type="string",  description="资讯ID", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200", description="成功",),
     *      @OA\Response(response="500", description="失败",),
     * )
     */
    public function detail()
    {
        $id      = request('id');
        $article = Article::where('id', $id)->where('status', 1)->firstOrFail();

        return $this->success('messages.success', [
            'article' => $article,
        ]);
    }
}
