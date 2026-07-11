<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->nullableMorphs('paymentable');
        });

        DB::statement('UPDATE payments SET paymentable_id = COALESCE(booking_id, order_id), paymentable_type = CASE WHEN booking_id IS NOT NULL THEN "App\\\\Models\\\\Booking" WHEN order_id IS NOT NULL THEN "App\\\\Models\\\\Order" END');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['booking_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
        });

        DB::statement('UPDATE payments SET booking_id = CASE WHEN paymentable_type = "App\\\\Models\\\\Booking" THEN paymentable_id END, order_id = CASE WHEN paymentable_type = "App\\\\Models\\\\Order" THEN paymentable_id END');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropMorphs('paymentable');
        });
    }
};
