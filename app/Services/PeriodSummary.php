<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Support\BudgetPeriod;
use App\Support\EntryWindow;
use Illuminate\Support\Collection;

/**
 * Everything the dashboard needs to know about one budget period.
 */
class PeriodSummary
{
    public float $totalBudget;

    public float $totalSpent;

    public float $remaining;

    public float $spentPercentage;

    public bool $isCurrent;

    public int $daysLeft = 0;

    public float $dailyAllowance = 0;

    public float $todaySpent = 0;

    /** Spending in the calendar month the period sits in (useful for weekly budgets). */
    public float $monthSpent;

    /**
     * @var Collection<int, array{category: Category, budget: float, spent: float, percentage: float|null, over: bool}>
     */
    public Collection $rows;

    public function __construct(public User $user, public BudgetPeriod $period)
    {
        $budgets = Budget::forPeriod($user, $period)->keyBy(fn (Budget $b) => $b->category_id ?? 'total');

        $spentByCategory = $this->spentByCategory(...$period->range());

        $this->totalBudget = (float) ($budgets['total']->amount ?? 0);
        $this->totalSpent = (float) $spentByCategory->sum();
        $this->remaining = $this->totalBudget - $this->totalSpent;
        $this->spentPercentage = $this->totalBudget > 0 ? $this->totalSpent / $this->totalBudget * 100 : 0;

        $this->rows = $user->categories()->ordered()->get()
            ->map(function (Category $category) use ($budgets, $spentByCategory) {
                $budget = (float) ($budgets[$category->id]->amount ?? 0);
                $spent = (float) ($spentByCategory[$category->id] ?? 0);

                return [
                    'category' => $category,
                    'budget' => $budget,
                    'spent' => $spent,
                    'percentage' => $budget > 0 ? $spent / $budget * 100 : null,
                    'over' => $budget > 0 && $spent > $budget,
                ];
            })
            ->filter(fn ($row) => $row['spent'] > 0 || $row['budget'] > 0)
            ->values();

        $today = EntryWindow::today($user);
        $this->isCurrent = $period->contains($today);

        if ($this->isCurrent) {
            $this->daysLeft = (int) $today->diffInDays($period->end) + 1;
            $this->dailyAllowance = $this->remaining > 0 ? $this->remaining / $this->daysLeft : 0;
            $this->todaySpent = (float) $this->expenses()->whereDate('date', $today->toDateString())->sum('amount');
        }

        $month = BudgetPeriod::make(BudgetPeriod::MONTHLY, $this->isCurrent ? $today : $period->start);
        $this->monthSpent = $period->isMonthly()
            ? $this->totalSpent
            : (float) $this->expenses()->whereBetween('date', $month->range())->sum('amount');
    }

    public function hasBudget(): bool
    {
        return $this->totalBudget > 0;
    }

    public function isOver(): bool
    {
        return $this->hasBudget() && $this->remaining < 0;
    }

    /**
     * The category that's furthest over its limit, if any.
     */
    public function worstCategory(): ?array
    {
        return $this->rows->where('over', true)->sortByDesc('percentage')->first();
    }

    private function spentByCategory(string $from, string $to): Collection
    {
        return $this->expenses()
            ->whereBetween('date', [$from, $to])
            ->groupBy('category_id')
            ->selectRaw('category_id, sum(amount) as total')
            ->pluck('total', 'category_id')
            ->map(fn ($total) => (float) $total);
    }

    private function expenses()
    {
        return Expense::where('user_id', $this->user->id);
    }
}
