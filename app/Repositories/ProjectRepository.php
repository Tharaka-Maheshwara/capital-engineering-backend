<?php

namespace App\Repositories;

use App\Models\Project;

class ProjectRepository
{
    protected $model;

    public function __construct(Project $project)
    {
        $this->model = $project;
    }

    public function findBySlug($slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
