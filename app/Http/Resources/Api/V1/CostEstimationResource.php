<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CostEstimationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'project_type' => $this->project_type,
            'sqft'         => $this->sqft,
            'budget_type'  => $this->budget_type,
            'soil'         => $this->soil,
            'design'       => $this->design,
            'stories'      => $this->stories,
            'roof'         => $this->roof,
            'base_cost'    => $this->base_cost,
            'total_cost'   => $this->total_cost,
            'created_at'   => $this->created_at?->toAtomString(),
        ];
    }
}
