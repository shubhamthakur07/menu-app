<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subscription Plans Master Table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('trial_days')->default(30);
            $table->text('description')->nullable();
            $table->string('badge_text')->nullable();
            $table->json('features')->nullable(); // Feature flags map: { enable_waiter_call: bool, enable_kds: bool, ... }
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Restaurant Subscriptions History & Active Snapshot Table (Grandfathering support)
        Schema::create('restaurant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('plan_name');
            $table->decimal('plan_price', 10, 2)->default(0);
            $table->string('billing_cycle')->default('trial'); // 'trial', 'monthly', 'yearly'
            $table->json('features_snapshot'); // Exact snapshot of features locked in for this current cycle
            $table->string('status')->default('trial'); // 'trial', 'active', 'grace', 'expired'
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('payment_method')->default('free_trial'); // 'free_trial', 'upi', 'cash', 'manual'
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Add subscription tracking columns to restaurants table
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('subscription_plan')->default('starter')->after('is_active');
            $table->string('subscription_status')->default('trial')->after('subscription_plan');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            $table->unsignedBigInteger('current_subscription_id')->nullable()->after('subscription_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'subscription_status',
                'trial_ends_at',
                'subscription_ends_at',
                'current_subscription_id',
            ]);
        });

        Schema::dropIfExists('restaurant_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
