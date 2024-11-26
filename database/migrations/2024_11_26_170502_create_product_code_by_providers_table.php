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
        Schema::create('product_code_by_providers', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('provider_id')->constrained('providers');
            $table->string('code');
            $table->primary(['product_id', 'provider_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_code_by_providers');
    }
};
