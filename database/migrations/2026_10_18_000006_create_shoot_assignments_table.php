<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shoot_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoot_id')->constrained('shoots')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('candid_photographer'); // lead_photographer, candid_photographer, traditional_photographer, cinematographer, drone_pilot, assistant
            $table->decimal('payout_agreed', 10, 2)->default(0);
            $table->string('status')->default('assigned'); // assigned, confirmed, declined, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoot_assignments');
    }
};
