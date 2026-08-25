<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $aksi = $request->query('aksi');
        $query = ActivityLog::with('user')->latest();

        if ($aksi) {
            $query->where('aksi', $aksi);
        }

        $logs = $query->paginate(20)->withQueryString();
        $distinctAksi = ActivityLog::select('aksi')->distinct()->pluck('aksi');

        return view('admin.activity-logs.index', compact('logs', 'distinctAksi', 'aksi'));
    }
}
