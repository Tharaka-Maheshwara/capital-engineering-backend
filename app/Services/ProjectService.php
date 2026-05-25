<?php

namespace App\Services;

use App\Repositories\ProjectRepository;

class ProjectService
{
    protected $repo;

    public function __construct(ProjectRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getBySlug($slug)
    {
        return $this->repo->findBySlug($slug);
    }
}
