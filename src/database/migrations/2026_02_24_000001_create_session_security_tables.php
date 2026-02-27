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
        // Tabel untuk tracking session activities (audit trail)
        Schema::create('session_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sso_user_id')->nullable();
            $table->string('session_id', 255);
            $table->string('ip_address', 45);
            $table->string('method', 10);                    // GET, POST, PUT, DELETE, PATCH
            $table->string('path', 255);
            $table->unsignedSmallInteger('status_code');    // HTTP status code
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes untuk query performance
            $table->index('sso_user_id');
            $table->string('user_name', 255)->nullable(); // optional, for easier querying without join
            $table->index('session_id');
            $table->index('ip_address');
            $table->index('created_at');

            // Composite index untuk common queries
            $table->index(['sso_user_id', 'created_at']);
        });

        // Tabel untuk security events (login attempts, ip mismatches, etc)
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50);               // login, logout, ip_mismatch, auth_failure
            $table->unsignedBigInteger('sso_user_id')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('ip_address', 45);
            $table->string('session_id', 255)->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('details')->nullable();           // JSON details
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('event_type');
            $table->index('sso_user_id');
            $table->string('user_name', 255)->nullable(); // optional, for easier querying without join
            $table->index('ip_address');
            $table->index(['created_at', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_activities');
        Schema::dropIfExists('security_events');
    }
};
