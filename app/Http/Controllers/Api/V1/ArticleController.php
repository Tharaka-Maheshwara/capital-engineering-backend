<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreArticleRequest;
use App\Http\Requests\Api\V1\UpdateArticleRequest;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function __construct(protected ArticleService $articleService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->integer('per_page', 12)));

        return ArticleResource::collection($this->articleService->paginate($perPage));
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($files = $request->file('images')) {
            $data['images'] = is_array($files) ? $files : [$files];
        }

        $article = $this->articleService->create($data);

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

    public function update(UpdateArticleRequest $request, Article $article): ArticleResource
    {
        $data = $request->validated();

        if ($files = $request->file('images')) {
            $data['images'] = is_array($files) ? $files : [$files];
        }

        $updatedArticle = $this->articleService->update($article, $data);

        return new ArticleResource($updatedArticle);
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->articleService->delete($article);

        return response()->json([], 204);
    }
}
