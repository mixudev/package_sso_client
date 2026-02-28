<?php

namespace Mixu\SSOAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SecurityNotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->session()->get('sso_user.id');

        $notifications = DB::table('security_notifications')
            ->when($userId, fn($q) => $q->where('sso_user_id', $userId))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('mixu-sso-auth::security.notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $userId = $request->session()->get('sso_user.id');

        $updated = DB::table('security_notifications')
            ->where('id', $id)
            ->when($userId, fn($q) => $q->where('sso_user_id', $userId))
            ->update(['is_read' => true]);

        return response()->json(['success' => (bool) $updated]);
    }

    public function markAllRead(Request $request)
    {
        $userId = $request->session()->get('sso_user.id');

        DB::table('security_notifications')
            ->when($userId, fn($q) => $q->where('sso_user_id', $userId))
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
