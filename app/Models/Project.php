<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CloudinaryService;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'type',
        'location',
        'client',
        'area',
        'price',
        'start_date',
        'end_date',
        'meta_description',
        'featured_image_alt',
        'featured_image_url',
        'featured_image_public_id',
        'gallery',
    ];

    protected $casts = [
        'gallery' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $project): void {
            try {
                $publicId = $project->featured_image_public_id;

                if ($publicId) {
                    app(CloudinaryService::class)->deleteProjectImage($publicId);
                }
            } catch (\Throwable $e) {
                // ignore errors during model deletion cleanup
            }
        });
    }
}
