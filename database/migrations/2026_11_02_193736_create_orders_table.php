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
    $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
    $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->nullOnDelete();
    $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
    $table->string('order_number', 30)->unique();
    $table->string('status', 20)->default('pending'); // pending, paid, processing, shipped, delivered, cancelled
    $table->decimal('subtotal', 12, 2);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('tax', 12, 2)->default(0);
    $table->decimal('shipping_cost', 12, 2)->default(0);
    $table->decimal('total', 12, 2);
    $table->text('shipping_address_snapshot')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index(['store_id', 'status']);
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
