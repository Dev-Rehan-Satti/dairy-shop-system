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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile_number');
            $table->text('address')->nullable();
            $table->string('area')->nullable();
            $table->string('customer_type');
            $table->string('status')->default('active');
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('last_payment_amount', 12, 2)->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('last_delivery_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
