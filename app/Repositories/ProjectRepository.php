<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    public function __construct(protected Project $model)
    {
    }

    public function create(array $attributes): Project
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->newQuery()->latest()->paginate($perPage);
    }
}
