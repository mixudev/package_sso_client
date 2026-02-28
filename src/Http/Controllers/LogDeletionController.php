<?php

namespace Mixu\SSOAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LogDeletionController extends Controller
{
    // Audit logs
    public function deleteAuditLog(Request $request, $id)
    {
        try {
            $deleted = DB::table('audit_logs')->where('id', $id)->delete();
            return back()->with($deleted ? 'status' : 'error', $deleted ? 'Log deleted' : 'Log not found');
        } catch (\Exception $e) {
            Log::error('Failed to delete audit log', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Could not delete log');
        }
    }

    public function deleteAuditLogsRange(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date',
        ]);
        try {
            $deleted = DB::table('audit_logs')
                ->whereDate('created_at', '>=', $data['from'])
                ->whereDate('created_at', '<=', $data['to'])
                ->delete();
            return back()->with('status', "Deleted {$deleted} logs from {$data['from']} to {$data['to']}");
        } catch (\Exception $e) {
            Log::error('Failed to delete audit logs range', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->with('error', 'Could not delete logs for the selected range');
        }
    }

    public function deleteAuditLogsDay(Request $request)
    {
        $data = $request->validate(['date' => 'required|date']);
        try {
            $deleted = DB::table('audit_logs')
                ->whereDate('created_at', $data['date'])
                ->delete();
            return back()->with('status', "Deleted {$deleted} logs for {$data['date']}");
        } catch (\Exception $e) {
            Log::error('Failed to delete audit logs for day', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->with('error', 'Could not delete logs for the selected day');
        }
    }

    public function deleteAuditLogsAll(Request $request)
    {
        $data = $request->validate(['confirm_all' => 'required|string']);
        if (($data['confirm_all'] ?? '') !== 'DELETE ALL') {
            return back()->with('error', 'Confirmation phrase mismatch. Type "DELETE ALL" to confirm.');
        }
        try {
            $deleted = DB::table('audit_logs')->delete();
            return back()->with('status', "Deleted all audit logs ({$deleted} records)");
        } catch (\Exception $e) {
            Log::error('Failed to delete all audit logs', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete all logs');
        }
    }

    // Session activities (page access)
    public function deletePageAccessLog(Request $request, $id)
    {
        try {
            $deleted = DB::table('session_activities')->where('id', $id)->delete();
            return back()->with($deleted ? 'status' : 'error', $deleted ? 'Log deleted' : 'Log not found');
        } catch (\Exception $e) {
            Log::error('Failed to delete page access log', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Could not delete log');
        }
    }

    public function deletePageAccessLogsRange(Request $request)
    {
        $data = $request->validate(['from' => 'required|date', 'to' => 'required|date']);
        try {
            $deleted = DB::table('session_activities')
                ->whereDate('created_at', '>=', $data['from'])
                ->whereDate('created_at', '<=', $data['to'])
                ->delete();
            return back()->with('status', "Deleted {$deleted} session activity logs from {$data['from']} to {$data['to']}");
        } catch (\Exception $e) {
            Log::error('Failed to delete page access logs range', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->with('error', 'Could not delete logs for the selected range');
        }
    }

    public function deletePageAccessLogsDay(Request $request)
    {
        $data = $request->validate(['date' => 'required|date']);
        try {
            $deleted = DB::table('session_activities')
                ->whereDate('created_at', $data['date'])
                ->delete();
            return back()->with('status', "Deleted {$deleted} session activity logs for {$data['date']}");
        } catch (\Exception $e) {
            Log::error('Failed to delete page access logs for day', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->with('error', 'Could not delete logs for the selected day');
        }
    }

    public function deletePageAccessLogsAll(Request $request)
    {
        $data = $request->validate(['confirm_all' => 'required|string']);
        if (($data['confirm_all'] ?? '') !== 'DELETE ALL') {
            return back()->with('error', 'Confirmation phrase mismatch. Type "DELETE ALL" to confirm.');
        }
        try {
            $deleted = DB::table('session_activities')->delete();
            return back()->with('status', "Deleted all session activity logs ({$deleted} records)");
        } catch (\Exception $e) {
            Log::error('Failed to delete all page access logs', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete all logs');
        }
    }

    // Security events
    public function deleteSecurityEvent(Request $request, $id)
    {
        try {
            $deleted = DB::table('security_events')->where('id', $id)->delete();
            return back()->with($deleted ? 'status' : 'error', $deleted ? 'Event deleted' : 'Event not found');
        } catch (\Exception $e) {
            Log::error('Failed to delete security event', ['id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Could not delete event');
        }
    }

    public function deleteSecurityEventsRange(Request $request)
    {
        $data = $request->validate(['from' => 'required|date','to' => 'required|date']);
        try {
            $deleted = DB::table('security_events')
                ->whereDate('created_at', '>=', $data['from'])
                ->whereDate('created_at', '<=', $data['to'])
                ->delete();
            return back()->with('status', "Deleted {$deleted} security events from {$data['from']} to {$data['to']}");
        } catch (\Exception $e) {
            Log::error('Failed to delete security events range', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->with('error', 'Could not delete events for the selected range');
        }
    }

    public function deleteSecurityEventsDay(Request $request)
    {
        $data = $request->validate(['date' => 'required|date']);
        try {
            $deleted = DB::table('security_events')
                ->whereDate('created_at', $data['date'])
                ->delete();
            return back()->with('status', "Deleted {$deleted} security events for {$data['date']}");
        } catch (\Exception $e) {
            Log::error('Failed to delete security events for day', ['error' => $e->getMessage(), 'input' => $request->all()]);
            return back()->with('error', 'Could not delete events for the selected day');
        }
    }

    public function deleteSecurityEventsAll(Request $request)
    {
        $data = $request->validate(['confirm_all' => 'required|string']);
        if (($data['confirm_all'] ?? '') !== 'DELETE ALL') {
            return back()->with('error', 'Confirmation phrase mismatch. Type "DELETE ALL" to confirm.');
        }
        try {
            $deleted = DB::table('security_events')->delete();
            return back()->with('status', "Deleted all security events ({$deleted} records)");
        } catch (\Exception $e) {
            Log::error('Failed to delete all security events', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete all events');
        }
    }
}
