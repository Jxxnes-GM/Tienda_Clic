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
        Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
    $table->string('method', 30); // card, pse, cash_on_delivery, transfer
    $table->string('status', 20)->default('pending'); // pending, approved, rejected, refunded
    $table->decimal('amount', 12, 2);
    $table->string('transaction_reference')->nullable();
    $table->json('gateway_response')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
