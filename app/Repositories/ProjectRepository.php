<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjectRepository
{
    public function __construct(protected Project $model)
    {
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->newQuery()->latest()->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Project
    {
        return $this->model->newQuery()->where('slug', $slug)->first();
    }

    public function findBySlugOrFail(string $slug): Project
    {
        $project = $this->findBySlug($slug);

        if (! $project) {
            throw (new ModelNotFoundException())->setModel(Project::class, [$slug]);
        }

        return $project;
    }
}
