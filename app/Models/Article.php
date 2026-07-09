<?php

namespace App\Models;

use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'youtube_link',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $article): void {
            try {
                foreach ($article->images ?? [] as $image) {
                    $publicId = is_array($image)
                        ? ($image['public_id'] ?? $image['publicId'] ?? null)
                        : null;

                    if (is_string($publicId) && $publicId !== '') {
                        app(CloudinaryService::class)->deleteProjectImage($publicId);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore cleanup errors so the database delete can continue.
            }
        });
    }
}
