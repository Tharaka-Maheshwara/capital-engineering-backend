<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFeedbackRequest;
use App\Http\Resources\Api\V1\FeedbackResource;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedbackController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(12, (int) $request->integer('per_page', 6)));

        return FeedbackResource::collection(
            Feedback::query()
                ->latest()
                ->take($perPage)
                ->get(),
        );
    }

    public function store(StoreFeedbackRequest $request): JsonResponse|FeedbackResource
    {
        $user = $this->resolveUserFromBearerToken($request);

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rating' => $request->integer('rating'),
            'message' => $request->string('message')->trim()->toString(),
        ]);

        return (new FeedbackResource($feedback))
            ->response()
            ->setStatusCode(201);
    }

    private function resolveUserFromBearerToken(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        $tokenHash = hash('sha256', $token);

        return User::query()
            ->where('api_token_hash', $tokenHash)
            ->where(function ($query) {
                $query->whereNull('api_token_expires_at')
                    ->orWhere('api_token_expires_at', '>', now());
            })
            ->first();
    }
}