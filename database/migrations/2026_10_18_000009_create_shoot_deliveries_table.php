<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shoot_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoot_id')->constrained('shoots')->cascadeOnDelete();
            $table->integer('raw_photos_count')->default(0);
            $table->string('raw_drive_link')->nullable();
            $table->integer('edited_photos_count')->default(0);
            $table->string('client_gallery_link')->nullable();
            $table->string('gallery_pin', 10)->nullable();
            $table->string('album_preview_link')->nullable();
            $table->text('video_links')->nullable();
            $table->string('delivery_status')->default('pending'); // pending, raw_uploaded, editing, proofing, delivered
            $table->dateTime('delivered_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoot_deliveries');
    }
};
