<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_activities', function (Blueprint $table) {
            // Additional indexes to speed up aggregations
            if (!Schema::hasColumn('session_activities','status_code')) {
                return;
            }

            // helper to check existing index
            $hasStatusIdx = collect(DB::select("SHOW INDEX FROM session_activities WHERE Key_name = ?", ['session_activities_status_code_index']))->isNotEmpty();
            if (! $hasStatusIdx) {
                $table->index('status_code');
            }

            $hasPathIdx = collect(DB::select("SHOW INDEX FROM session_activities WHERE Key_name = ?", ['session_activities_path_index']))->isNotEmpty();
            if (! $hasPathIdx) {
                $table->index('path');
            }
        });

        Schema::table('security_events', function (Blueprint $table) {
            $hasIdx = collect(DB::select("SHOW INDEX FROM security_events WHERE Key_name = ?", ['security_events_event_type_index']))->isNotEmpty();
            if (! $hasIdx) {
                $table->index('event_type');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $hasIdx = collect(DB::select("SHOW INDEX FROM audit_logs WHERE Key_name = ?", ['audit_logs_created_at_index']))->isNotEmpty();
            if (! $hasIdx) {
                $table->index('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('session_activities', function (Blueprint $table) {
            $table->dropIndex(['status_code']);
            $table->dropIndex(['path']);
        });

        Schema::table('security_events', function (Blueprint $table) {
            $table->dropIndex(['event_type']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};