<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('currency')->default('₹');
            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('upi_name')->nullable();
            $table->integer('table_count')->default(10);
            $table->decimal('tax_percentage', 5, 2)->default(5.00); // e.g. 5% GST
            $table->boolean('enable_online_payment')->default(true);
            $table->boolean('enable_cash_payment')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
