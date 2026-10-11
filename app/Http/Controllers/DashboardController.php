<?php

namespace App\Http\Controllers;

use App\Services\PeriodSummary;
use App\Support\BudgetPeriod;
use App\Support\EntryWindow;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $period = BudgetPeriod::fromKey($user, $request->query('period'));
        $summary = new PeriodSummary($user, $period);

        $entries = $user->expenses()
            ->with('category')
            ->whereBetween('date', $period->range())
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('dashboard', [
            'summary' => $summary,
            'period' => $period,
            'entries' => $entries,
            'categories' => $user->categories()->active()->ordered()->get(),
            'openRange' => EntryWindow::openRange($user),
        ]);
    }
}
