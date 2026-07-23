<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_estimations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('project_type');      // house, villa, renovation, commercial
            $table->unsignedInteger('sqft');
            $table->string('budget_type');        // budget-friendly, semi-luxury, luxury
            $table->string('soil');               // normal, poor
            $table->string('design');             // simple, complex
            $table->string('stories');            // 1, 2, 3
            $table->string('roof');               // slab, hiped, multy-gable
            $table->decimal('base_cost', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_estimations');
    }
};
