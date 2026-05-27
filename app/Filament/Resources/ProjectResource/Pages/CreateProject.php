<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected UploadedFile|string|null $featuredImage = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->featuredImage = $data['featured_image'] ?? null;
        unset($data['featured_image']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

        $this->uploadFeaturedImageForRecord($record);

        return $record->refresh();
    }

    private function uploadFeaturedImageForRecord(Project $record): void
    {
        if ($this->featuredImage === null || $this->featuredImage === '') {
            return;
        }

        $cloudinary = app(CloudinaryService::class);
        $folder = 'projects/'.$record->getKey();

        if ($this->featuredImage instanceof UploadedFile) {
            $upload = $cloudinary->uploadProjectImage($this->featuredImage, $folder);
        } else {
            $fullPath = Storage::disk('local')->path($this->featuredImage);
            $upload = $cloudinary->uploadProjectImageFilePath($fullPath, $folder);
            Storage::disk('local')->delete($this->featuredImage);
        }

        $record->forceFill([
            'featured_image_url' => $upload['url'] ?? null,
            'featured_image_public_id' => $upload['public_id'] ?? null,
        ])->save();
    }
}