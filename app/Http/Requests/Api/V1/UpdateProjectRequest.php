<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        $project = $this->route('project');
        $projectId = is_object($project) ? $project->getKey() : $project;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:50000'],
            'status' => ['sometimes', 'required', Rule::in(['planning', 'ongoing', 'completed'])],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'client' => ['sometimes', 'required', 'string', 'max:255'],
            'area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:160'],
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