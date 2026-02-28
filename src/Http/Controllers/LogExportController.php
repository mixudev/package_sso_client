<?php

namespace Mixu\SSOAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class LogExportController extends Controller
{
    /**
     * Show combined log list with filters and pagination.
     */
    public function index(Request $request)
    {
        $rawFrom = $request->query('from');
        $rawTo = $request->query('to');
        $from = $rawFrom ? str_replace('T',' ',$rawFrom) : null;
        $to = $rawTo ? str_replace('T',' ',$rawTo) : null;
        $perPage = 50;

        $queryFunc = function () use ($from, $to) {
            $queries = [];

            $audit = DB::table('audit_logs')
                ->selectRaw("'audit' as log_type, id, created_at, user_id, email, action as description, ip_address, method, path, status_code, details");
            if ($from) { $audit->where('created_at', '>=', $from); }
            if ($to) { $audit->where('created_at', '<=', $to); }
            $queries[] = $audit->get();

            $events = DB::table('security_events')
                ->selectRaw("'event' as log_type, id, created_at, sso_user_id as user_id, email, event_type as description, ip_address, session_id as path, NULL as method, NULL as status_code, details");
            if ($from) { $events->where('created_at', '>=', $from); }
            if ($to) { $events->where('created_at', '<=', $to); }
            $queries[] = $events->get();

            $access = DB::table('session_activities')
                ->selectRaw("'access' as log_type, id, created_at, sso_user_id as user_id, NULL as email, path as description, ip_address, method, path, status_code, NULL as details");
            if ($from) { $access->where('created_at', '>=', $from); }
            if ($to) { $access->where('created_at', '<=', $to); }
            $queries[] = $access->get();

            return collect($queries)->flatten(1);
        };

        $all = $queryFunc();
        $sorted = $all->sortByDesc('created_at')->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $sorted->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($items, $sorted->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('mixu-sso-auth::security.export-logs', [
            'logs' => $paginated,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Stream CSV export of combined logs (human readable).
     */
    public function download(Request $request)
    {
        $rawFrom = $request->query('from');
        $rawTo = $request->query('to');
        $from = $rawFrom ? str_replace('T',' ',$rawFrom) : null;
        $to = $rawTo ? str_replace('T',' ',$rawTo) : null;

        return response()->stream(function () use ($from, $to) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Timestamp','Type','User ID','Email','Description','IP','Method','Path','Status','Details']);

            $sources = [
                ['table'=>'audit_logs','type'=>'audit','map'=>function($r){
                    return [
                        $r->created_at,
                        'audit',
                        $r->user_id,
                        $r->email,
                        $r->action,
                        $r->ip_address,
                        $r->method,
                        $r->path,
                        $r->status_code,
                        $r->details,
                    ];
                }],
                ['table'=>'security_events','type'=>'event','map'=>function($r){
                    return [
                        $r->created_at,
                        'event',
                        $r->sso_user_id,
                        $r->email,
                        $r->event_type,
                        $r->ip_address,
                        '',
                        $r->session_id,
                        '',
                        $r->details,
                    ];
                }],
                ['table'=>'session_activities','type'=>'access','map'=>function($r){
                    return [
                        $r->created_at,
                        'access',
                        $r->sso_user_id,
                        '',
                        $r->path,
                        $r->ip_address,
                        $r->method,
                        $r->path,
                        $r->status_code,
                        '',
                    ];
                }],
            ];

            foreach ($sources as $src) {
                $query = DB::table($src['table']);
                if ($from) { $query->where('created_at','>=',$from); }
                if ($to) { $query->where('created_at','<=',$to); }
                $query->orderBy('created_at');
                foreach ($query->cursor() as $row) {
                    fputcsv($output, $src['map']($row));
                }
            }
            fclose($output);
        }, 200, [
            'Content-Type'=>'text/csv',
            'Content-Disposition'=>'attachment; filename="logs-export-'.now()->format('YmdHis').'.csv"',
        ]);
    }
}
