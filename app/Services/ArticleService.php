<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    public function __construct(
        protected ArticleRepository $repo,
        protected CloudinaryService $cloudinaryService,
    ) {
    }

    public function create(array $attributes): Article
    {
        return DB::transaction(function () use ($attributes) {
            $base = $attributes;
            unset($base['images']);

            $article = $this->repo->create($base);

            $uploadedImages = [];

            try {
                $uploadedImages = $this->uploadImages($attributes['images'] ?? [], $article->getKey());

                if ($uploadedImages !== []) {
                    $article = $this->repo->update($article, [
                        'images' => $uploadedImages,
                    ]);
                }
            } catch (\Throwable $e) {
                $this->deleteImages($uploadedImages);
                Log::error('Cloudinary upload failed during article create: ' . $e->getMessage(), ['exception' => $e]);

                throw $e;
            }

            return $article;
        });
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }

    public function update(Article $article, array $attributes): Article
    {
        return DB::transaction(function () use ($article, $attributes) {
            $existingImages = $article->images ?? [];
            $base = $attributes;
            unset($base['images']);

            $updatedArticle = $this->repo->update($article, $base);

            if (!isset($attributes['images'])) {
                return $updatedArticle;
            }

            $uploadedImages = [];

            try {
                $uploadedImages = $this->uploadImages($attributes['images'] ?? [], $article->getKey());

                $updatedArticle = $this->repo->update($updatedArticle, [
                    'images' => $uploadedImages,
                ]);

                $this->deleteImages($existingImages);
            } catch (\Throwable $e) {
                $this->deleteImages($uploadedImages);
                Log::error('Cloudinary upload failed during article update: ' . $e->getMessage(), ['exception' => $e]);

                throw $e;
            }

            return $updatedArticle;
        });
    }

    public function delete(Article $article): void
    {
        $this->repo->delete($article);
    }

    /**
     * @param array<int, mixed> $files
     * @return array<int, array{public_id?: string, url?: string}>
     */
    private function uploadImages(array $files, ?int $articleId = null): array
    {
        $folder = $articleId ? 'articles/' . $articleId : 'articles';
        $images = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $upload = $this->cloudinaryService->uploadProjectImage($file, $folder);
            $publicId = $upload['public_id'] ?? null;
            $url = $upload['url'] ?? null;

            if ($publicId !== null || $url !== null) {
                $images[] = array_filter([
                    'public_id' => $publicId,
                    'url' => $url,
                ]);
            }
        }

        return $images;
    }

    /**
     * @param array<int, mixed> $images
     */
    private function deleteImages(array $images): void
    {
        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }

            $publicId = $image['public_id'] ?? $image['publicId'] ?? null;

            if (is_string($publicId) && $publicId !== '') {
                $this->cloudinaryService->deleteProjectImage($publicId);
            }
        }
    }
}
