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
        Schema::table('users', function (Blueprint $table) {
        $table->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
        $table->string('role', 20)->default('customer')->after('password'); // customer, admin, super_admin
        $table->string('phone', 20)->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropConstrainedForeignId('store_id');
        $table->dropColumn(['role', 'phone']);
        });
    }
};
