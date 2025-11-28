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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_cart')->nullable();
            $table->bigInteger('id_product')->nullable();
            $table->bigInteger('id_product_attribute')->nullable();
            $table->bigInteger('id_store')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_attribute_name')->nullable();
            $table->integer('qty')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
