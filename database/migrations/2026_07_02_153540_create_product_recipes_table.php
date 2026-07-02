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
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');
            $table->string('bottle_size');
            $table->string('ratio_type');
            
            $table->foreignUuid('bibit_material_id')->constrained('materials');
            $table->decimal('bibit_volume', 10, 2);
            
            $table->foreignUuid('campuran_material_id')->nullable()->constrained('materials');
            $table->decimal('campuran_volume', 10, 2)->default(0.00);
            
            $table->foreignUuid('botol_material_id')->constrained('materials');
            
            $table->decimal('selling_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_recipes');
    }
};
