<?php

namespace App\Services;

use App\Models\Design;
use App\Repositories\DesignRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DesignService
{
    public function __construct(
        protected DesignRepository $repo,
        protected CloudinaryService $cloudinaryService,
    ) {
    }

    public function create(array $attributes): Design
    {
        return DB::transaction(function () use ($attributes) {
            $base = $attributes;
            unset($base['images']);

            $design = $this->repo->create($base);

            $uploadedImages = [];

            try {
                $uploadedImages = $this->uploadImages($attributes['images'] ?? [], $design->getKey());

                if ($uploadedImages !== []) {
                    $design = $this->repo->update($design, [
                        'images' => $uploadedImages,
                    ]);
                }
            } catch (\Throwable $e) {
                $this->deleteImages($uploadedImages);
                Log::error('Cloudinary upload failed during design create: ' . $e->getMessage(), ['exception' => $e]);

                throw $e;
            }

            return $design;
        });
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }

    public function update(Design $design, array $attributes): Design
    {
        return DB::transaction(function () use ($design, $attributes) {
            $existingImages = $design->images ?? [];
            $base = $attributes;
            unset($base['images']);

            $updatedDesign = $this->repo->update($design, $base);

            if (!isset($attributes['images'])) {
                return $updatedDesign;
            }

            $uploadedImages = [];

            try {
                $uploadedImages = $this->uploadImages($attributes['images'] ?? [], $design->getKey());

                $updatedDesign = $this->repo->update($updatedDesign, [
                    'images' => $uploadedImages,
                ]);

                $this->deleteImages($existingImages);
            } catch (\Throwable $e) {
                $this->deleteImages($uploadedImages);
                Log::error('Cloudinary upload failed during design update: ' . $e->getMessage(), ['exception' => $e]);

                throw $e;
            }

            return $updatedDesign;
        });
    }

    public function delete(Design $design): void
    {
        $this->repo->delete($design);
    }

    /**
     * @param array<int, mixed> $files
     * @return array<int, array{public_id?: string, url?: string}>
     */
    private function uploadImages(array $files, ?int $designId = null): array
    {
        $folder = $designId ? 'designs/' . $designId : 'designs';
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