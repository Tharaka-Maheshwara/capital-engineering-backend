<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCostEstimationRequest;
use App\Http\Resources\Api\V1\CostEstimationResource;
use App\Models\CostEstimation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CostEstimationController extends Controller
{
    /**
     * List all cost estimations (paginated, for admin panel).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(50, (int) $request->integer('per_page', 15)));

        $query = CostEstimation::query()->latest();

        // Search filter
        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('project_type', 'like', "%{$search}%");
            });
        }

        return CostEstimationResource::collection(
            $query->paginate($perPage),
        );
    }

    /**
     * Store a new cost estimation (public — anyone using the estimator).
     */
    public function store(StoreCostEstimationRequest $request): JsonResponse
    {
        $estimation = CostEstimation::create($request->validated());

        return (new CostEstimationResource($estimation))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Delete a cost estimation record.
     */
    public function destroy(CostEstimation $costEstimation): JsonResponse
    {
        $costEstimation->delete();

        return response()->json(['message' => 'Cost estimation deleted successfully.']);
    }
}
