<?php

namespace App\Models;

use App\Services\CloudinaryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_category',
        'sub_categories',
        'description',
        'images',
    ];

    protected $casts = [
        'sub_categories' => 'array',
        'images' => 'array',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $design): void {
            try {
                foreach ($design->images ?? [] as $image) {
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

    public static function categoryMap(): array
    {
        return [
            'Residential Designs' => [
                'Modern Single-Story Houses',
                'Luxury Two-Story / Multi-Story Houses',
                'Budget-Friendly / Low Cost Houses',
                'Apartment & Condominium Units',
                'Tiny Houses / Eco-Friendly Homes',
            ],
            'Commercial Designs' => [
                'Office Buildings / Corporate Spaces',
                'Retail Stores & Showrooms',
                'Hotels, Restaurants & Cafes',
                'Warehouses & Industrial Buildings',
            ],
            'Interior Designs' => [
                'Living Room & Bedroom Concepts',
                'Modern Kitchen & Pantry Designs',
                'Bathroom & Washroom Layouts',
                'Office Interior & Lighting Concepts',
            ],
            'Exterior & Landscaping' => [
                'Front Elevation Designs',
                'Garden & Courtyard Designs',
                'Swimming Pools & Outdoor Lounges',
                'Gate & Boundary Wall Designs',
            ],
            'Architectural & Structural Plans' => [
                '2D Floor Plans & Blueprints',
                '3D Realistic Renderings / Virtual Tours',
                'MEP Drawings',
            ],
        ];
    }

    public static function subCategoriesFor(?string $mainCategory): array
    {
        if (!is_string($mainCategory) || $mainCategory === '') {
            return [];
        }

        return self::categoryMap()[$mainCategory] ?? [];
    }
}