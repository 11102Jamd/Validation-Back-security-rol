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
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ID_order')->constrained('order')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('ID_product')->constrained('product')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('RequestedQuantity');
            $table->double('PriceQuantity',10,3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail');
    }
};
