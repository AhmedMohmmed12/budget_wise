<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\GoalContribution;
use App\Models\Income;
use App\Models\User;
use App\Support\BudgetPeriod;
use App\Support\EntryWindow;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = EntryWindow::today($user);

        $firstDate = collect([
            Expense::where('user_id', $user->id)->min('date'),
            Income::where('user_id', $user->id)->min('date'),
        ])->filter()->min();
        $firstYear = $firstDate ? (int) substr($firstDate, 0, 4) : $today->year;

        $year = (int) $request->query('year', $today->year);
        $year = max($firstYear, min($today->year, $year));

        $months = $this->months($user, $year, $today);

        return view('reports.index', [
            'year' => $year,
            'years' => range($today->year, min($firstYear, $today->year)),
            'months' => $months,
            'totals' => [
                'income' => $months->sum('income'),
                'spent' => $months->sum('spent'),
                'budget' => $months->sum('budget'),
                'setAside' => $months->sum('setAside'),
                'kept' => $months->sum('kept'),
            ],
            'barMax' => max(1, $months->max(fn ($m) => max($m['spent'], $m['budget']))),
            'categories' => $this->categoryComparison($user, $today),
            'weeks' => $user->budget_period === BudgetPeriod::WEEKLY ? $this->weeks($user) : collect(),
            'today' => $today,
        ]);
    }

    /**
     * One row per month of the year, up to the current month.
     */
    private function months(User $user, int $year, CarbonImmutable $today): Collection
    {
        $from = "$year-01-01";
        $to = "$year-12-31";

        $spent = $this->sumsByMonth(Expense::class, $user, $from, $to);
        $income = $this->sumsByMonth(Income::class, $user, $from, $to);
        $setAside = $this->sumsByMonth(GoalContribution::class, $user, $from, $to);
        $budgets = $this->totalBudgets($user);

        $last = $year === $today->year ? $today->month : 12;

        return collect(range(1, $last))->map(function (int $m) use ($year, $spent, $income, $setAside, $budgets, $user) {
            $start = CarbonImmutable::create($year, $m, 1);
            $key = $start->format('Y-m');

            $budget = $user->budget_period === BudgetPeriod::WEEKLY
                ? $this->weeksStartingIn($user, $start)->sum(fn (BudgetPeriod $w) => $this->budgetAt($budgets, BudgetPeriod::WEEKLY, $w->start))
                : $this->budgetAt($budgets, BudgetPeriod::MONTHLY, $start);

            $row = [
                'key' => $key,
                'label' => $start->format('F'),
                'income' => $income[$key] ?? 0.0,
                'spent' => $spent[$key] ?? 0.0,
                'budget' => $budget,
                'setAside' => $setAside[$key] ?? 0.0,
            ];

            return $row + ['kept' => $row['income'] - $row['spent'] - $row['setAside']];
        })->reverse()->values();
    }

    /**
     * This month vs last month vs the three months before, per category.
     */
    private function categoryComparison(User $user, CarbonImmutable $today): Collection
    {
        $thisMonth = $today->startOfMonth();
        $lastMonth = $thisMonth->subMonthNoOverflow();
        $from = $thisMonth->subMonthsNoOverflow(3);

        $rows = Expense::query()->toBase()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$from->toDateString(), $today->endOfMonth()->toDateString()])
            ->selectRaw('category_id, date, sum(amount) as total')
            ->groupBy('category_id', 'date')
            ->get();

        $sum = fn ($categoryId, CarbonImmutable $start, CarbonImmutable $end) => (float) $rows
            ->where('category_id', $categoryId)
            ->filter(fn ($r) => substr($r->date, 0, 10) >= $start->toDateString() && substr($r->date, 0, 10) <= $end->toDateString())
            ->sum('total');

        return $user->categories()->ordered()->get()
            ->map(fn ($category) => [
                'category' => $category,
                'this' => $sum($category->id, $thisMonth, $thisMonth->endOfMonth()),
                'last' => $sum($category->id, $lastMonth, $lastMonth->endOfMonth()),
                'average' => $sum($category->id, $from, $lastMonth->endOfMonth()) / 3,
            ])
            ->filter(fn ($row) => $row['this'] > 0 || $row['last'] > 0 || $row['average'] > 0)
            ->values();
    }

    /**
     * The last twelve weeks for weekly budgets.
     */
    private function weeks(User $user): Collection
    {
        $week = BudgetPeriod::current($user);
        $weeks = collect([$week]);
        for ($i = 0; $i < 11; $i++) {
            $weeks->push($week = $week->previous());
        }

        $daily = Expense::query()->toBase()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$weeks->last()->start->toDateString(), $weeks->first()->end->toDateString()])
            ->selectRaw('date, sum(amount) as total')
            ->groupBy('date')
            ->get();
        $budgets = $this->totalBudgets($user);

        return $weeks->map(fn (BudgetPeriod $w) => [
            'period' => $w,
            'spent' => (float) $daily->filter(fn ($r) => $w->contains(substr($r->date, 0, 10)))->sum('total'),
            'budget' => $this->budgetAt($budgets, BudgetPeriod::WEEKLY, $w->start),
        ]);
    }

    /**
     * @param  class-string  $model
     * @return Collection<string, float> keyed by Y-m
     */
    private function sumsByMonth(string $model, User $user, string $from, string $to): Collection
    {
        // toBase() keeps the soft-delete scope, so trashed entries are left out.
        return $model::query()->toBase()->where('user_id', $user->id)->whereBetween('date', [$from, $to])
            ->selectRaw('date, sum(amount) as total')->groupBy('date')->get()
            ->groupBy(fn ($r) => substr($r->date, 0, 7))
            ->map(fn ($days) => (float) $days->sum('total'));
    }

    /**
     * All total budgets, newest first, for looking up the one in force on a date.
     */
    private function totalBudgets(User $user): Collection
    {
        return Budget::where('user_id', $user->id)->whereNull('category_id')
            ->orderByDesc('period_start')->get(['period', 'period_start', 'amount']);
    }

    /**
     * The budget in force for a period: its own, or the last one before it
     * (budgets carry forward until changed).
     */
    private function budgetAt(Collection $budgets, string $type, CarbonImmutable $start): float
    {
        $budget = $budgets->first(fn ($b) => $b->period === $type && $b->period_start->toDateString() <= $start->toDateString());

        return (float) ($budget->amount ?? 0);
    }

    private function weeksStartingIn(User $user, CarbonImmutable $month): Collection
    {
        $weeks = collect();
        $week = BudgetPeriod::make(BudgetPeriod::WEEKLY, $month, (int) $user->week_starts_on);
        if ($week->start->lt($month)) {
            $week = $week->next();
        }

        while ($week->start->month === $month->month && $week->start->year === $month->year) {
            $weeks->push($week);
            $week = $week->next();
        }

        return $weeks;
    }
}
