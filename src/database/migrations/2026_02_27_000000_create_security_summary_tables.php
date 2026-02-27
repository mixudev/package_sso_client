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
        Schema::create('security_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedBigInteger('requests')->default(0);
            $table->unsignedBigInteger('logins')->default(0);
            $table->unsignedBigInteger('failed')->default(0);
            $table->timestamps();

            $table->index('date');
        });

        Schema::create('security_hourly_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedTinyInteger('hour');
            $table->unsignedBigInteger('requests')->default(0);
            $table->unsignedBigInteger('logins')->default(0);
            $table->timestamps();

            $table->unique(['date','hour']);
            $table->index(['date','hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_hourly_stats');
        Schema::dropIfExists('security_daily_stats');
    }
};