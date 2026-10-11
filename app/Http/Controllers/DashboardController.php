<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\BudgetSummaryService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get budget summary
        $budgetService = new BudgetSummaryService($user->id);
        $budgetSummary = $budgetService->getSummary();

        // Get recent expenses (last 10) - Optimized with select
        $recentExpenses = Expense::select('id', 'user_id', 'amount', 'category', 'date', 'note', 'created_at')
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard', compact('budgetSummary', 'recentExpenses'));
    }
}
