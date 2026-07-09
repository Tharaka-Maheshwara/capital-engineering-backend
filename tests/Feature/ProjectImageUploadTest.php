<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Models\Project;

class ProjectImageUploadTest extends TestCase
{
    use RefreshDatabase;
    public function test_api_creates_project_with_image()
    {
        Storage::fake('local');

        // bind CloudinaryService to a fake that returns deterministic data
        $this->app->bind(\App\Services\CloudinaryService::class, function () {
            return new class extends \App\Services\CloudinaryService {
                public function uploadProjectImage(\Illuminate\Http\UploadedFile $file, string $folder = 'projects'): array {
                    return ['url' => 'https://example.com/image.jpg', 'public_id' => 'projects/example123'];
                }

                public function uploadProjectImageFilePath(string $path, string $folder = 'projects'): array {
                    return ['url' => 'https://example.com/image.jpg', 'public_id' => 'projects/example123'];
                }

                public function deleteProjectImage(?string $publicId): void { return; }
            };
        });

        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/v1/projects', [
            'title' => 'Test create',
            'description' => 'desc',
            'status' => 'planning',
            'location' => 'Colombo',
            'client' => 'ACME',
            'featured_image' => $file,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('projects', [
            'title' => 'Test create',
            'featured_image_public_id' => 'projects/example123',
        ]);
    }
}
