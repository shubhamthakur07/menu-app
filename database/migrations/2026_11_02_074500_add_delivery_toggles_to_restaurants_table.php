<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurants', 'enable_home_delivery')) {
                $table->boolean('enable_home_delivery')->default(true)->after('enable_cash_payment');
            }
            if (!Schema::hasColumn('restaurants', 'enable_takeaway')) {
                $table->boolean('enable_takeaway')->default(true)->after('enable_home_delivery');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            if (Schema::hasColumn('restaurants', 'enable_home_delivery')) {
                $table->dropColumn('enable_home_delivery');
            }
            if (Schema::hasColumn('restaurants', 'enable_takeaway')) {
                $table->dropColumn('enable_takeaway');
            }
        });
    }
};
