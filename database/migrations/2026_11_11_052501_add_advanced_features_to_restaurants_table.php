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
            $table->boolean('enable_waiter_call')->default(true)->after('is_active');
            $table->boolean('enable_google_reviews')->default(true)->after('enable_waiter_call');
            $table->string('google_review_url')->nullable()->after('enable_google_reviews');
            $table->boolean('enable_whatsapp_bill')->default(true)->after('google_review_url');
            $table->boolean('enable_split_bill')->default(true)->after('enable_whatsapp_bill');
            $table->boolean('enable_voice_search')->default(true)->after('enable_split_bill');
            $table->boolean('enable_kds')->default(true)->after('enable_voice_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'enable_waiter_call',
                'enable_google_reviews',
                'google_review_url',
                'enable_whatsapp_bill',
                'enable_split_bill',
                'enable_voice_search',
                'enable_kds',
            ]);
        });
    }
};
