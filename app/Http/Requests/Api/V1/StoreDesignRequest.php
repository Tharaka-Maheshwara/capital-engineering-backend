<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Design;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $images = $this->file('images');
        if ($images && !is_array($images)) {
            $this->files->set('images', [$images]);
        }

        if ($images) {
            $this->merge([
                'images' => is_array($images) ? $images : [$images],
            ]);
        }

        $this->merge([
            'main_category' => $this->sanitizeText($this->input('main_category')),
            'sub_categories' => $this->sanitizeTextArray($this->input('sub_categories')),
            'description' => $this->sanitizeText($this->input('description')),
        ]);
    }

    public function rules(): array
    {
        return [
            'main_category' => ['required', 'string', 'max:255', Rule::in(array_keys(Design::categoryMap()))],
            'description' => ['nullable', 'string', 'max:255'],
            'sub_categories' => ['required', 'array', 'min:1'],
            'sub_categories.*' => ['string', 'max:255', Rule::in($this->allowedSubCategories())],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['file', 'image', 'max:2048'],
        ];
    }

    private function allowedSubCategories(): array
    {
        return Design::subCategoriesFor($this->input('main_category'));
    }

    private function sanitizeText(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim(strip_tags((string) $value));
    }

    private function sanitizeTextArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $cleaned = [];

        foreach ($value as $item) {
            $sanitized = $this->sanitizeText($item);
            if ($sanitized !== null && $sanitized !== '') {
                $cleaned[] = $sanitized;
            }
        }

        return array_values(array_unique($cleaned));
    }
}