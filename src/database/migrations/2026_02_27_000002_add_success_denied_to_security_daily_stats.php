<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_daily_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('successful')->default(0)->after('requests');
            $table->unsignedBigInteger('denied')->default(0)->after('successful');
        });
    }

    public function down(): void
    {
        Schema::table('security_daily_stats', function (Blueprint $table) {
            $table->dropColumn('successful');
            $table->dropColumn('denied');
        });
    }
};