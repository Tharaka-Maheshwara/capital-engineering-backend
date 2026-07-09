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
        $galleryFiles = $this->file('gallery_images');
        if ($galleryFiles && !is_array($galleryFiles)) {
            $this->files->set('gallery_images', [$galleryFiles]);
        }
        if ($galleryFiles) {
            $this->merge([
                'gallery_images' => is_array($galleryFiles) ? $galleryFiles : [$galleryFiles],
            ]);
        }

        $this->merge([
            'title' => $this->sanitizeText($this->input('title')),
            'description' => $this->sanitizeHtml($this->input('description')),
            'location' => $this->sanitizeText($this->input('location')),
            'client' => $this->sanitizeText($this->input('client')),
            'area' => $this->sanitizeText($this->input('area')),
            'type' => $this->sanitizeText($this->input('type')),
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
            'price' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'type' => ['required', 'string', 'in:commercial,residential,industrial'],
            'featured_image' => ['nullable', 'file', 'image', 'max:2048'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'gallery_images' => ['sometimes', 'array'],
            'gallery_images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif,image/jpg', 'max:2048'],
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
