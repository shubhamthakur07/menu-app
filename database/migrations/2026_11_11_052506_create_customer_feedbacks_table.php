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
        Schema::create('customer_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('order_code', 50)->nullable();
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('redirected_to_google')->default(false);
            $table->timestamps();

            $table->index(['restaurant_id', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_feedbacks');
    }
};
