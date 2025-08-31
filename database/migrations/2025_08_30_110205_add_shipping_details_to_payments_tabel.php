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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('shipping_service')->nullable()->after('address');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('shipping_service');
            $table->string('shipping_status')->default('processing')->after('status')->comment('processing, shipped, delivered, cancelled');
            $table->string('tracking_number')->nullable()->after('shipping_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['shipping_service', 'shipping_cost', 'shipping_status', 'tracking_number']);
        });
    }
};
