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
        Schema::create('coupons', function (Blueprint $table) {
     $table->id();
         $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
         $table->string('code', 50);
         $table->string('type', 20); // percentage, fixed
         $table->decimal('value', 12, 2);
         $table->decimal('min_order_amount', 12, 2)->default(0);
         $table->unsignedInteger('max_uses')->nullable();
         $table->unsignedInteger('used_count')->default(0);
         $table->timestamp('starts_at')->nullable();
         $table->timestamp('expires_at')->nullable();
         $table->boolean('is_active')->default(true);
         $table->timestamps();

     $table->unique(['store_id', 'code']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
