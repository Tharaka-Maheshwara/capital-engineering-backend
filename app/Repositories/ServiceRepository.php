<?php

namespace App\Repositories;

use App\Models\Service;

class ServiceRepository
{
    protected $model;

    public function __construct(Service $service)
    {
        $this->model = $service;
    }

    public function all()
    {
        return $this->model->all();
    }
}
