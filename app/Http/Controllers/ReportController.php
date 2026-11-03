<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Shoot;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $totalShootsCount = Shoot::count();
        $totalRevenue = Payment::where('status', 'verified')->sum('amount');
        $totalBalance = Shoot::whereNotIn('status', ['completed', 'cancelled'])->sum('balance_amount');

        // Lead Conversion Rate
        $totalLeads = Lead::count();
        $convertedLeads = Lead::where('status', 'converted')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Shoots Breakdown by Category
        $shootsByCategory = Shoot::selectRaw('event_type, count(*) as count, sum(total_amount) as total')
            ->groupBy('event_type')
            ->get();

        // Photographers Workload
        $photographers = User::where('role', 'photographer')
            ->withCount('assignments')
            ->get();

        // Recent studio activity logs
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->take(10)->get();

        // Monthly revenue collections
        $isSqlite = config('database.default') === 'sqlite';
        $monthExpr = $isSqlite ? "strftime('%Y-%m', paid_at)" : "DATE_FORMAT(paid_at, '%b %Y')";

        $monthlyRevenue = Payment::where('status', 'verified')
            ->selectRaw("{$monthExpr} as month_name, sum(amount) as total")
            ->groupBy('month_name')
            ->orderByRaw('min(paid_at) desc')
            ->take(6)
            ->get();

        return view('reports.index', compact(
            'totalShootsCount',
            'totalRevenue',
            'totalBalance',
            'totalLeads',
            'convertedLeads',
            'conversionRate',
            'shootsByCategory',
            'photographers',
            'activityLogs',
            'monthlyRevenue'
        ));
    }
}
