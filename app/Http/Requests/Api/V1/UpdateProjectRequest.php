<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
            'slug' => $this->normalizeSlug($this->input('slug') ?: $this->input('title')),
            'description' => $this->sanitizeHtml($this->input('description')),
            'location' => $this->sanitizeText($this->input('location')),
            'client' => $this->sanitizeText($this->input('client')),
            'area' => $this->sanitizeText($this->input('area')),
            'featured_image' => $this->sanitizePath($this->input('featured_image')),
            'gallery' => $this->sanitizeGallery($this->input('gallery')),
            'meta_description' => $this->sanitizeText($this->input('meta_description')),
        ]);
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $projectId = is_object($project) ? $project->getKey() : $project;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($projectId)],
            'description' => ['sometimes', 'required', 'string', 'max:50000'],
            'status' => ['sometimes', 'required', Rule::in(['planning', 'ongoing', 'completed'])],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'client' => ['sometimes', 'required', 'string', 'max:255'],
            'area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'featured_image' => ['sometimes', 'required', 'string', 'max:2048'],
            'gallery' => ['sometimes', 'nullable', 'array'],
            'gallery.*' => ['string', 'max:2048'],
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

    private function normalizeSlug(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Str::slug((string) $value);
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

    private function sanitizeGallery(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        return array_values(array_filter(array_map(fn ($item) => $this->sanitizePath($item), $value)));
    }
}