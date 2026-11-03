<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('Wedding'); // Wedding, Pre-Wedding, Maternity, Corporate, Birthday, Commercial
            $table->decimal('price', 10, 2);
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->integer('duration_hours')->default(8);
            $table->integer('photographers_count')->default(1);
            $table->integer('videographers_count')->default(0);
            $table->boolean('drone_included')->default(false);
            $table->integer('album_count')->default(1);
            $table->text('deliverables')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
