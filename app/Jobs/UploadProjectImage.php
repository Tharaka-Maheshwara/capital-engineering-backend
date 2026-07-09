<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\CloudinaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadProjectImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $path, protected int $projectId, protected ?string $previousPublicId = null, protected ?string $alt = null)
    {
        $this->onQueue(config('queue.default'));
    }

    public function handle(CloudinaryService $cloudinary): void
    {
        $fullPath = Storage::path($this->path);

        // perform upload
        $response = $cloudinary->uploadProjectImageFilePath($fullPath, 'projects/'.$this->projectId);

        // update project
        $project = Project::find($this->projectId);
        if (! $project) {
            return;
        }

        $project->featured_image_url = $response['url'] ?? null;
        $project->featured_image_public_id = $response['public_id'] ?? null;
        if ($this->alt !== null) {
            $project->featured_image_alt = $this->alt;
        }

        $project->save();

        // delete previous if different
        if ($this->previousPublicId && $this->previousPublicId !== ($response['public_id'] ?? null)) {
            try {
                $cloudinary->deleteProjectImage($this->previousPublicId);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // cleanup temp file
        try {
            Storage::delete($this->path);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
