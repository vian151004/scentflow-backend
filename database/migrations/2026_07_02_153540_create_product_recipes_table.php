<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');
            $table->string('bottle_size');
            $table->string('ratio_type');
            
            $table->foreignUuid('fragrance_material_id')->nullable()->constrained('materials');
            $table->decimal('fragrance_volume', 10, 2);
            
            $table->foreignUuid('mixture_material_id')->nullable()->constrained('materials');
            $table->decimal('mixture_volume', 10, 2)->default(0.00);
            
            $table->foreignUuid('bottle_material_id')->constrained('materials');
            
            $table->decimal('selling_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_recipes');
    }
};