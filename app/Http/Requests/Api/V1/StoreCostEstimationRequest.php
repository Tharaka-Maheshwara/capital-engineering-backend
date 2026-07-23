<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostEstimationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:30'],
            'email'        => ['required', 'email', 'max:255'],
            'project_type' => ['required', 'string', 'in:house,villa,renovation,commercial'],
            'sqft'         => ['required', 'integer', 'min:1'],
            'budget_type'  => ['required', 'string', 'in:budget-friendly,semi-luxury,luxury'],
            'soil'         => ['required', 'string', 'in:normal,poor'],
            'design'       => ['required', 'string', 'in:simple,complex'],
            'stories'      => ['required', 'string', 'in:1,2,3'],
            'roof'         => ['required', 'string', 'in:slab,hiped,multy-gable'],
            'base_cost'    => ['required', 'numeric', 'min:0'],
            'total_cost'   => ['required', 'numeric', 'min:0'],
        ];
    }
}
