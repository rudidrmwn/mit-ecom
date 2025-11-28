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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_user');   
            $table->string('email')->nullable();             
            $table->bigInteger('id_address')->nullable();
            $table->string('status')->nullable();
            $table->float('total_amount',8,2)->default(0);
            $table->float('discount_amount',8,2)->default(0);
            $table->float('tax_amount',8,2)->default(0);
            $table->float('final_amount', 8,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
