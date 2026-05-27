<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\EditRecord;
use App\Services\CloudinaryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected UploadedFile|string|null $featuredImage = null;

    protected ?string $previousPublicId = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->featuredImage = $data['featured_image'] ?? null;
        $this->previousPublicId = $this->record?->featured_image_public_id ?? null;
        unset($data['featured_image']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        $this->uploadFeaturedImageForRecord($record);

        return $record->refresh();
    }

    private function uploadFeaturedImageForRecord(Model $record): void
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

        if ($this->previousPublicId !== null && $this->previousPublicId !== ($upload['public_id'] ?? null)) {
            try {
                $cloudinary->deleteProjectImage($this->previousPublicId);
            } catch (\Throwable $e) {
                // ignore deletion errors to avoid breaking the save
            }
        }
    }
}