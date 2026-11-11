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
        Schema::table('restaurants', function (Blueprint $table) {
            if (! Schema::hasColumn('restaurants', 'loyalty_enabled')) {
                $table->boolean('loyalty_enabled')->default(true)->after('allow_shared_table_orders');
            }
            if (! Schema::hasColumn('restaurants', 'loyalty_required_visits')) {
                $table->unsignedInteger('loyalty_required_visits')->default(5)->after('loyalty_enabled');
            }
            if (! Schema::hasColumn('restaurants', 'loyalty_game_type')) {
                $table->string('loyalty_game_type', 30)->default('spinner')->after('loyalty_required_visits');
            }
            if (! Schema::hasColumn('restaurants', 'loyalty_rewards')) {
                $table->json('loyalty_rewards')->nullable()->after('loyalty_game_type');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'loyalty_reward_claimed')) {
                $table->boolean('loyalty_reward_claimed')->default(false)->after('game_reward_claimed');
            }
            if (! Schema::hasColumn('orders', 'loyalty_reward_details')) {
                $table->json('loyalty_reward_details')->nullable()->after('loyalty_reward_claimed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_enabled',
                'loyalty_required_visits',
                'loyalty_game_type',
                'loyalty_rewards',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_reward_claimed',
                'loyalty_reward_details',
            ]);
        });
    }
};
