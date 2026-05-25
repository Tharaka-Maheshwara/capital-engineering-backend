<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(protected ProjectRepository $repo)
    {
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Project
    {
        return $this->repo->findBySlug($slug);
    }

    public function findBySlugOrFail(string $slug): Project
    {
        return $this->repo->findBySlugOrFail($slug);
    }
}
