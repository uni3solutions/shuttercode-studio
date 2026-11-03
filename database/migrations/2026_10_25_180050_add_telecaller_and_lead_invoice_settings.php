<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('studio_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->unique('user_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('notes')->constrained('leads')->nullOnDelete();
        });

        Schema::table('shoots', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('package_id')->constrained('leads')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('customer_id')->constrained('leads')->nullOnDelete();
            $table->foreignId('telecaller_id')->nullable()->after('lead_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['telecaller_id']);
            $table->dropForeign(['lead_id']);
            $table->dropColumn(['telecaller_id', 'lead_id']);
        });

        Schema::table('shoots', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
        });

        Schema::table('studio_settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
