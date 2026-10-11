<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Expense;
use Carbon\Carbon;

class BudgetSummaryService
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get comprehensive budget summary for the user
     */
    public function getSummary()
    {
        // Optimized query - select only needed columns
        $budgets = Budget::select('id', 'user_id', 'category', 'amount', 'period', 'start_date')
            ->where('user_id', $this->userId)
            ->where('active', true)
            ->get();

        // Get total budget
        $totalBudget = $budgets->where('category', 'Total')->first();
        $totalBudgetAmount = $totalBudget ? (float) $totalBudget->amount : 0;
        $period = $totalBudget ? $totalBudget->period : 'monthly';

        // Calculate date range based on period
        if ($period === 'weekly') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Get total spent in the current period
        $totalSpent = Expense::where('user_id', $this->userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
        $totalSpent = (float) $totalSpent;

        // Calculate remaining and percentage
        $remaining = $totalBudgetAmount - $totalSpent;
        $spentPercentage = $totalBudgetAmount > 0 ? ($totalSpent / $totalBudgetAmount) * 100 : 0;

        // Calculate days remaining
        $daysRemaining = Carbon::now()->diffInDays($endDate) + 1;

        // Calculate daily budget
        $dailyBudget = $daysRemaining > 0 ? $remaining / $daysRemaining : 0;

        // Get today's spending
        $todaySpending = Expense::where('user_id', $this->userId)
            ->whereDate('date', Carbon::today())
            ->sum('amount');
        $todaySpending = (float) $todaySpending;

        // Get category summaries
        $categorySummaries = $this->getCategorySummaries($budgets, $startDate, $endDate);

        return [
            'totalBudget' => $totalBudgetAmount,
            'totalSpent' => $totalSpent,
            'remaining' => $remaining,
            'spentPercentage' => $spentPercentage,
            'dailyBudget' => $dailyBudget,
            'daysRemaining' => $daysRemaining,
            'todaySpending' => $todaySpending,
            'categorySummaries' => $categorySummaries,
            'period' => $period,
        ];
    }

    /**
     * Get spending summary by category
     */
    protected function getCategorySummaries($budgets, $startDate, $endDate)
    {
        $categories = ['Restaurants', 'Housing', 'Transportation', 'Shopping', 'Entertainment', 'Others'];
        $summaries = [];

        foreach ($categories as $category) {
            $budget = $budgets->where('category', $category)->first();
            $budgetAmount = $budget ? (float) $budget->amount : 0;

            $spent = Expense::where('user_id', $this->userId)
                ->where('category', $category)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');
            $spent = (float) $spent;

            $percentage = $budgetAmount > 0 ? ($spent / $budgetAmount) * 100 : 0;

            $summaries[] = [
                'category' => $category,
                'budget' => $budgetAmount,
                'spent' => $spent,
                'percentage' => $percentage,
            ];
        }

        return $summaries;
    }
}
