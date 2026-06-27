<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
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
            'title' => $this->sanitizeText($this->input('title')),
            'description' => $this->sanitizeHtml($this->input('description')),
            'youtube_link' => $this->sanitizeText($this->input('youtube_link')),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:50000'],
            'youtube_link' => ['nullable', 'string', 'max:255'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['file', 'image', 'max:4096'],
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
}
