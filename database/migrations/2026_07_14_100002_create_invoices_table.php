<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // draft | validated | posted | failed
            $table->string('invoice_type')->default('Sale Invoice');
            $table->date('invoice_date');
            $table->string('buyer_ntn_cnic')->nullable();
            $table->string('buyer_business_name');
            $table->string('buyer_province');
            $table->string('buyer_address');
            $table->string('buyer_registration_type')->default('Registered');
            $table->string('invoice_ref_no')->nullable();
            $table->string('scenario_id')->default('SN001');
            $table->string('fbr_invoice_number')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
