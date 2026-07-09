<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CloudinaryService
{
    private ?Cloudinary $client = null;

    public function uploadProjectImage(UploadedFile $file, string $folder = 'projects'): array
    {
        $options = [
            'folder' => $folder,
            'resource_type' => 'image',
            'fetch_format' => 'auto',
            'quality' => 'auto',
        ];

        $response = $this->client()->uploadApi()->upload($file->getRealPath(), $options);

        return [
            'url' => $response['secure_url'] ?? null,
            'public_id' => $response['public_id'] ?? null,
        ];
    }

    /**
     * Upload by a filesystem path (used by queued job).
     */
    public function uploadProjectImageFilePath(string $path, string $folder = 'projects'): array
    {
        $options = [
            'folder' => $folder,
            'resource_type' => 'image',
            'fetch_format' => 'auto',
            'quality' => 'auto',
        ];

        $response = $this->client()->uploadApi()->upload($path, $options);

        return [
            'url' => $response['secure_url'] ?? null,
            'public_id' => $response['public_id'] ?? null,
        ];
    }

    public function deleteProjectImage(?string $publicId): void
    {
        if ($publicId === null || $publicId === '') {
            return;
        }

        $this->client()->adminApi()->deleteAssets($publicId, [
            'resource_type' => 'image',
        ]);
    }

    private function client(): Cloudinary
    {
        if ($this->client instanceof Cloudinary) {
            return $this->client;
        }

        $cloudinaryUrl = (string) env('CLOUDINARY_URL', '');

        if ($cloudinaryUrl === '') {
            throw new RuntimeException('CLOUDINARY_URL is not configured.');
        }

        return $this->client = new Cloudinary($cloudinaryUrl);
    }
}