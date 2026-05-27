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
            'featured_image_url' => $this->featured_image_url,
            'featured_image_public_id' => $this->featured_image_public_id,
            'featured_image_alt' => $this->featured_image_alt,
            'featured_image_thumbnail' => $this->buildCloudinaryUrl($this->featured_image_public_id, 400),
            'featured_image_og' => $this->buildCloudinaryUrl($this->featured_image_public_id, 1200),
            'gallery' => $this->normalizeGallery($this->gallery),
            'published_at' => $this->created_at?->toAtomString(),
        ];
    }

    private function getCloudName(): ?string
    {
        $url = env('CLOUDINARY_URL', '') ?: env('CLOUDINARY_CLOUD_NAME', '');

        if ($url === '') {
            return null;
        }

        // CLOUDINARY_URL format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
        if (str_starts_with($url, 'cloudinary://')) {
            $parts = explode('@', $url);
            return $parts[1] ?? null;
        }

        return $url;
    }

    private function buildCloudinaryUrl(?string $publicId, int $width = 800): ?string
    {
        if ($publicId === null) {
            return null;
        }

        $cloud = $this->getCloudName();
        if ($cloud === null) {
            return null;
        }

        $encodedId = rawurlencode($publicId);
        return "https://res.cloudinary.com/{$cloud}/image/upload/w_{$width},f_auto,q_auto/{$encodedId}";
    }

    private function normalizeGallery(mixed $gallery): array
    {
        if (!is_array($gallery)) {
            return [];
        }

        $normalized = [];

        foreach ($gallery as $item) {
            if (is_string($item) && $item !== '') {
                if (str_starts_with($item, 'http://') || str_starts_with($item, 'https://')) {
                    $normalized[] = $item;
                    continue;
                }

                $url = $this->buildCloudinaryUrl($item, 400);
                if ($url !== null) {
                    $normalized[] = $url;
                }

                continue;
            }

            if (is_array($item)) {
                $url = $item['secure_url'] ?? $item['url'] ?? null;
                if (is_string($url) && $url !== '') {
                    $normalized[] = $url;
                    continue;
                }

                $publicId = $item['public_id'] ?? $item['publicId'] ?? null;
                if (is_string($publicId) && $publicId !== '') {
                    $built = $this->buildCloudinaryUrl($publicId, 400);
                    if ($built !== null) {
                        $normalized[] = $built;
                    }
                }
            }
        }

        return $normalized;
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
