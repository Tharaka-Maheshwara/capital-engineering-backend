<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\UploadProjectImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(
        protected ProjectRepository $repo,
        protected CloudinaryService $cloudinaryService,
    )
    {
    }

    public function create(array $attributes): Project
    {
        return DB::transaction(function () use ($attributes) {
            // create base project without uploaded file attributes
            $base = $attributes;
            unset($base['featured_image']);
            unset($base['gallery_images']);

            $project = $this->repo->create($base);

            try {
                // pass created project id so upload goes into project-specific folder
                $attributes = $this->storeFeaturedImage($attributes, $project->getKey());
                $attributes = $this->storeGalleryImages($attributes, [], $project->getKey());

                // update project with image fields if present
                if (isset($attributes['featured_image_url']) || isset($attributes['featured_image_public_id'])) {
                    $project = $this->repo->update($project, [
                        'featured_image_url' => $attributes['featured_image_url'] ?? null,
                        'featured_image_public_id' => $attributes['featured_image_public_id'] ?? null,
                    ]);
                }

                if (isset($attributes['gallery'])) {
                    $project = $this->repo->update($project, [
                        'gallery' => $attributes['gallery'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Cloudinary upload failed during project create: '.$e->getMessage(), ['exception' => $e]);
                // rollback transaction and rethrow
                throw $e;
            }

            return $project;
        });
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage);
    }

    public function update(Project $project, array $attributes): Project
    {
        return DB::transaction(function () use ($project, $attributes) {
            $previousPublicId = $project->featured_image_public_id;
            $existingGallery = $project->gallery ?? [];

            // keep base attributes separate to avoid passing UploadedFile into repo directly
            $base = $attributes;
            unset($base['featured_image']);
            unset($base['gallery_images']);

            $updatedProject = $this->repo->update($project, $base);

            try {
                $attributes = $this->storeFeaturedImage($attributes, $project->getKey(), $previousPublicId);
                $attributes = $this->storeGalleryImages($attributes, $existingGallery, $project->getKey());

                if (isset($attributes['featured_image_url']) || isset($attributes['featured_image_public_id'])) {
                    $updatedProject = $this->repo->update($updatedProject, [
                        'featured_image_url' => $attributes['featured_image_url'] ?? null,
                        'featured_image_public_id' => $attributes['featured_image_public_id'] ?? null,
                    ]);
                }

                if (isset($attributes['gallery'])) {
                    $updatedProject = $this->repo->update($updatedProject, [
                        'gallery' => $attributes['gallery'],
                    ]);
                }

                if ($previousPublicId !== null && $previousPublicId !== $updatedProject->featured_image_public_id) {
                    $this->cloudinaryService->deleteProjectImage($previousPublicId);
                }
            } catch (\Throwable $e) {
                Log::error('Cloudinary upload failed during project update: '.$e->getMessage(), ['exception' => $e]);
                throw $e;
            }

            return $updatedProject;
        });
    }

    public function delete(Project $project): void
    {
        $this->cloudinaryService->deleteProjectImage($project->featured_image_public_id);
        $this->repo->delete($project);
    }

    private function storeFeaturedImage(array $attributes, ?int $projectId = null, ?string $previousPublicId = null): array
    {
        $featuredImage = $attributes['featured_image'] ?? null;

        $async = (bool) env('CLOUDINARY_ASYNC', false);

        if ($featuredImage instanceof UploadedFile) {
            $folder = $projectId ? 'projects/'.$projectId : 'projects';

            if ($async) {
                // store temporarily in storage and dispatch job
                $tmpPath = 'temp/uploads/'.Str::uuid()->toString().'.'.$featuredImage->getClientOriginalExtension();
                Storage::putFileAs(dirname($tmpPath), $featuredImage, basename($tmpPath));

                // dispatch job
                UploadProjectImage::dispatch($tmpPath, $projectId ?? 0, $previousPublicId, $attributes['featured_image_alt'] ?? null);

                // mark as deferred; job will update DB
                $attributes['featured_image_deferred'] = true;
            } else {
                $upload = $this->cloudinaryService->uploadProjectImage($featuredImage, $folder);

                $attributes['featured_image_url'] = $upload['url'];
                $attributes['featured_image_public_id'] = $upload['public_id'];

                if ($previousPublicId !== null && $previousPublicId !== $upload['public_id']) {
                    $this->cloudinaryService->deleteProjectImage($previousPublicId);
                }
            }
        }

        unset($attributes['featured_image']);

        return $attributes;
    }

    private function storeGalleryImages(array $attributes, array $existingGallery = [], ?int $projectId = null): array
    {
        $galleryFiles = $attributes['gallery_images'] ?? null;

        if (!is_array($galleryFiles) || $galleryFiles === []) {
            unset($attributes['gallery_images']);
            return $attributes;
        }

        $folder = $projectId ? 'projects/'.$projectId.'/gallery' : 'projects/gallery';
        $gallery = $existingGallery;

        foreach ($galleryFiles as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $upload = $this->cloudinaryService->uploadProjectImage($file, $folder);
            $publicId = $upload['public_id'] ?? null;
            $url = $upload['url'] ?? null;

            if ($publicId !== null || $url !== null) {
                $gallery[] = array_filter([
                    'public_id' => $publicId,
                    'url' => $url,
                ]);
            }
        }

        if ($gallery !== []) {
            $attributes['gallery'] = array_values($gallery);
        }

        unset($attributes['gallery_images']);

        return $attributes;
    }
}
