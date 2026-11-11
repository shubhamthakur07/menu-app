<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0.00)->after('subtotal');
            }
            if (! Schema::hasColumn('orders', 'discount_reason')) {
                $table->string('discount_reason', 150)->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('orders', 'game_reward_claimed')) {
                $table->boolean('game_reward_claimed')->default(false)->after('discount_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'discount_reason', 'game_reward_claimed']);
        });
    }
};
