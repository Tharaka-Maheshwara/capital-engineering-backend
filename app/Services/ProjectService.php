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

    public function create(array $attributes): Project
    {
        return $this->repo->create($attributes);
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }
}
