<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->sanitizeText($this->input('title')),
            'description' => $this->sanitizeHtml($this->input('description')),
            'location' => $this->sanitizeText($this->input('location')),
            'client' => $this->sanitizeText($this->input('client')),
            'area' => $this->sanitizeText($this->input('area')),
            'meta_description' => $this->sanitizeText($this->input('meta_description')),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:50000'],
            'status' => ['required', Rule::in(['planning', 'ongoing', 'completed'])],
            'location' => ['required', 'string', 'max:255'],
            'client' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    private function sanitizeText(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim(strip_tags((string) $value));
    }

    private function sanitizeHtml(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $html = trim((string) $value);
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\\1>#is', '', $html) ?? $html;

        return trim($html);
    }

    private function sanitizePath(mixed $value): ?string
    {
        return $this->sanitizeText($value);
    }
}
