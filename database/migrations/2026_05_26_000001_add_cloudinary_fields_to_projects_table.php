<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('featured_image_url')->nullable()->after('meta_description');
            $table->string('featured_image_public_id')->nullable()->after('featured_image_url');
            $table->json('gallery')->nullable()->after('featured_image_public_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['featured_image_url', 'featured_image_public_id', 'gallery']);
        });
    }
};