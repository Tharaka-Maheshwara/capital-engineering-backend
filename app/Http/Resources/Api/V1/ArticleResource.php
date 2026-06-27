<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->sanitizeText($this->title),
            'description' => $this->description, // Description might contain HTML formatting, so let's keep it as is
            'youtube_link' => $this->sanitizeText($this->youtube_link),
            'images' => $this->normalizeImages($this->images),
            'image_urls' => $this->normalizeImageUrls($this->images),
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }

    private function normalizeImages(mixed $images): array
    {
        if (!is_array($images)) {
            return [];
        }

        $normalized = [];

        foreach ($images as $item) {
            if (is_string($item) && $item !== '') {
                $normalized[] = [
                    'url' => $item,
                    'public_id' => null,
                ];

                continue;
            }

            if (!is_array($item)) {
                continue;
            }

            $url = $item['url'] ?? $item['secure_url'] ?? null;
            $publicId = $item['public_id'] ?? $item['publicId'] ?? null;

            if (!is_string($url) || $url === '') {
                $url = $this->buildCloudinaryUrl(is_string($publicId) ? $publicId : null);
            }

            if ($url !== null) {
                $normalized[] = [
                    'url' => $url,
                    'public_id' => is_string($publicId) ? $publicId : null,
                ];
            }
        }

        return $normalized;
    }

    private function normalizeImageUrls(mixed $images): array
    {
        return array_values(array_filter(array_map(
            static fn (array $image): ?string => $image['url'] ?? null,
            $this->normalizeImages($images)
        )));
    }

    private function getCloudName(): ?string
    {
        $url = env('CLOUDINARY_URL', '') ?: env('CLOUDINARY_CLOUD_NAME', '');

        if ($url === '') {
            return null;
        }

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

    private function sanitizeText(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim(strip_tags((string) $value));
    }
}
