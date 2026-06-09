<?php

namespace App\Repositories;

use App\Models\Design;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DesignRepository
{
    public function __construct(protected Design $model)
    {
    }

    public function create(array $attributes): Design
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->newQuery()->latest()->paginate($perPage);
    }

    public function update(Design $design, array $attributes): Design
    {
        $design->fill($attributes);
        $design->save();

        return $design->fresh();
    }

    public function delete(Design $design): void
    {
        $design->delete();
    }
}