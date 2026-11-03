<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_settings', function (Blueprint $table) {
            $table->id();
            $table->string('studio_name')->default('ShutterCode Studio');
            $table->string('studio_tagline')->default('Fine Art Photography & Cinematography');
            $table->string('logo_path')->nullable();
            $table->string('phone')->default('+91 98450 00001');
            $table->string('email')->default('billing@shuttercode.com');
            $table->string('address')->default('84, 100 Feet Road, Indiranagar');
            $table->string('city')->default('Bengaluru');
            $table->string('state')->default('Karnataka');
            $table->string('pincode')->default('560038');
            $table->string('gstin')->default('29AAAAA0000A1Z5');
            $table->decimal('default_tax_rate', 5, 2)->default(18.00);
            $table->string('hsn_sac_code')->default('9983');
            $table->string('bank_name')->default('HDFC Bank Ltd');
            $table->string('bank_account_name')->default('ShutterCode Studio Private Limited');
            $table->string('bank_account_number')->default('50200012345678');
            $table->string('bank_ifsc')->default('HDFC0001234');
            $table->string('bank_branch')->default('Indiranagar Branch, Bengaluru');
            $table->string('upi_id')->default('shuttercode@hdfcbank');
            $table->string('invoice_prefix')->default('SHSN');
            $table->text('invoice_terms')->nullable();
            $table->text('invoice_notes')->nullable();
            $table->string('authorized_signatory_name')->default('Rahul Rao');
            $table->string('authorized_signatory_title')->default('Studio Director & Lead Artist');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_settings');
    }
};
