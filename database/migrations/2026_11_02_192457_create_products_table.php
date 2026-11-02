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
        Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
    $table->foreignId('category_id')->constrained('categories');
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->decimal('price', 12, 2);
    $table->unsignedInteger('stock')->default(0);
    $table->unsignedInteger('min_stock')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique(['store_id', 'slug']);
    $table->index(['store_id', 'is_active']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
