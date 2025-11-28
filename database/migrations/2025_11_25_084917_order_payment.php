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
        Schema::create('order_payment', function (Blueprint $table) {
            $table->id();
            $table->string('id_order')->nullable();                
            $table->bigInteger('id_address')->nullable();
            $table->bigInteger('id_payment_method')->nullable();
            $table->string('status')->nullable();
            $table->float('amount',8,2)->nullable();
            $table->string('gateway_reference')->nullable();
            $table->timestamp('paid_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payment');
    }
};
