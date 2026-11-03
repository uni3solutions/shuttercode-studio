<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('event_type');
            $table->date('event_date')->nullable();
            $table->string('venue')->nullable();
            $table->decimal('estimated_budget', 10, 2)->nullable();
            $table->string('source')->default('WhatsApp Meta'); // WhatsApp Meta, Website, Instagram, Walk-in, Referral
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new'); // new, contacted, in_discussion, proposal_sent, converted, lost
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
