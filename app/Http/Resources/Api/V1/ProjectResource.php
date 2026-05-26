<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->sanitizeText($this->title),
            'status' => $this->status,
            'status_label' => ucfirst((string) $this->status),
            'location' => $this->sanitizeText($this->location),
            'client' => $this->sanitizeText($this->client),
            'area' => $this->area !== null ? $this->sanitizeText($this->area) : null,
            'description' => $this->sanitizeHtml($this->description),
            'meta_description' => $this->meta_description !== null ? $this->sanitizeText($this->meta_description) : null,
            'published_at' => $this->created_at?->toAtomString(),
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

        return strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><blockquote><a><span><h1><h2><h3><h4><h5><h6>');
    }
}
