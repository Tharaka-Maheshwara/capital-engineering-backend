<?php

namespace App\Repositories;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleRepository
{
    public function __construct(protected Article $model)
    {
    }

    public function create(array $attributes): Article
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->newQuery()->latest()->paginate($perPage);
    }

    public function update(Article $article, array $attributes): Article
    {
        $article->fill($attributes);
        $article->save();

        return $article->fresh();
    }

    public function delete(Article $article): void
    {
        $article->delete();
    }
}
