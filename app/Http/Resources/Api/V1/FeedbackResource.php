<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'rating' => $this->rating,
            'message' => $this->message,
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}