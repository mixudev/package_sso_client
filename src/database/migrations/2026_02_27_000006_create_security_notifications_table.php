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
        Schema::create('security_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sso_user_id')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('event_type', 100)->nullable();
            $table->enum('severity', ['low','medium','high','critical'])->default('medium');
            $table->string('title', 255)->nullable();
            $table->longText('message')->nullable();
            $table->longText('details')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('sso_user_id');
            $table->index('is_read');
            $table->index('severity');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_notifications');
    }
};
