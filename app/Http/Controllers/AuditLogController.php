<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs (Admin only).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::query()->with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        return view('audit_logs.index', compact('logs'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog): View
    {
        $this->authorize('view', $auditLog);

        $auditLog->load('user');

        return view('audit_logs.show', compact('auditLog'));
    }
}
