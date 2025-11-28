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
            $table->bigInteger('id_order');                
            $table->bigInteger('id_address');
            $table->bigInteger('id_payment_method');
            $table->string('status');
            $table->float('amount',8,2);
            $table->string('gateway_reference');
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
