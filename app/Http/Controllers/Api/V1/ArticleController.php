<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;



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

    public function show(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

    public function store(Request $request): JsonResponse
    {
        // Proper validation would require a StoreArticleRequest class.
        $article = $this->articleService->create($request->all());

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Article $article): ArticleResource
    {
        // Proper validation would require an UpdateArticleRequest class.
        $updatedArticle = $this->articleService->update($article, $request->all());
        return new ArticleResource($updatedArticle);
    }

    public function destroy(Article $article): JsonResponse
    {
        // For now, we just need to read data.
        // This is a placeholder implementation.
        $this->articleService->delete($article);
        return response()->json(status: 204);
    }
}