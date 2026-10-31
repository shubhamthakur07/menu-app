<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('business_type', 50)->default('food')->after('name'); // 'food', 'salon', 'retail'
            $table->integer('slot_duration_minutes')->default(30)->after('table_count');
            $table->string('opening_time', 10)->default('09:00')->after('slot_duration_minutes');
            $table->string('closing_time', 10)->default('21:00')->after('opening_time');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->after('table_number');
            $table->string('booking_time', 20)->nullable()->after('booking_date');
            $table->string('chair_or_specialist', 100)->nullable()->after('booking_time');
            $table->integer('token_number')->nullable()->after('chair_or_specialist');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->integer('duration_minutes')->default(30)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'slot_duration_minutes', 'opening_time', 'closing_time']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'booking_time', 'chair_or_specialist', 'token_number']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['duration_minutes']);
        });
    }
};
