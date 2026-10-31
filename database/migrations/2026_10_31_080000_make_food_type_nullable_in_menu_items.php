<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE menu_items MODIFY food_type VARCHAR(50) NULL DEFAULT 'veg'");

        if (!Schema::hasColumn('menu_items', 'target_audience')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->string('target_audience', 50)->nullable()->default('all')->after('duration_minutes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'target_audience')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('target_audience');
            });
        }
    }
};
