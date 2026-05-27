<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProjectRequest;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projectService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->integer('per_page', 12)));

        return ProjectResource::collection($this->projectService->paginate($perPage));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($file = $request->file('featured_image')) {
            $data['featured_image'] = $file;
        }

        $project = $this->projectService->create($data);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $data = $request->validated();
        if ($file = $request->file('featured_image')) {
            $data['featured_image'] = $file;
        }

        $updatedProject = $this->projectService->update($project, $data);

        return new ProjectResource($updatedProject);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->projectService->delete($project);

        return response()->json(status: 204);
    }
}
