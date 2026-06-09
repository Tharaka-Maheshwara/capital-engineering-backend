<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDesignRequest;
use App\Http\Requests\Api\V1\UpdateDesignRequest;
use App\Http\Resources\Api\V1\DesignResource;
use App\Models\Design;
use App\Services\DesignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DesignController extends Controller
{
    public function __construct(protected DesignService $designService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->integer('per_page', 12)));

        return DesignResource::collection($this->designService->paginate($perPage));
    }

    public function store(StoreDesignRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($files = $request->file('images')) {
            $data['images'] = is_array($files) ? $files : [$files];
        }

        $design = $this->designService->create($data);

        return (new DesignResource($design))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Design $design): DesignResource
    {
        return new DesignResource($design);
    }

    public function update(UpdateDesignRequest $request, Design $design): DesignResource
    {
        $data = $request->validated();

        if ($files = $request->file('images')) {
            $data['images'] = is_array($files) ? $files : [$files];
        }

        $updatedDesign = $this->designService->update($design, $data);

        return new DesignResource($updatedDesign);
    }

    public function destroy(Design $design): JsonResponse
    {
        $this->designService->delete($design);

        return response()->json([], 204);
    }
}