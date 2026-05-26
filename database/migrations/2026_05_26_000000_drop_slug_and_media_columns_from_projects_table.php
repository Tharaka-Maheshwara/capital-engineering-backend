<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['slug', 'featured_image', 'gallery']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('slug')->unique()->after('title');
            $table->string('featured_image')->after('area');
            $table->json('gallery')->nullable()->after('featured_image');
        });
    }
};