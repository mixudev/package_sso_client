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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('action', 100);                  // page_access, file_download, data_export, login, logout, etc
            $table->string('entity_type', 100)->nullable(); // User, Post, Order, etc
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable();       // GET, POST, PUT, DELETE, PATCH
            $table->string('path', 255)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->longText('old_values')->nullable();    // JSON for before values
            $table->longText('new_values')->nullable();    // JSON for after values
            $table->enum('result', ['success', 'failed', 'denied'])->default('success');
            $table->longText('details')->nullable();        // JSON additional information
            $table->timestamp('created_at')->useCurrent();

            // Indexes untuk query performance
            $table->index('user_id');
            $table->index('action');
            $table->index('entity_type');
            $table->index('result');
            $table->index('ip_address');
            $table->index('created_at');

            // Composite indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
