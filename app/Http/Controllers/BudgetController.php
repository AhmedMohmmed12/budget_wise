<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\PeriodSummary;
use App\Support\BudgetPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $period = BudgetPeriod::current($user);

        $budgets = Budget::forPeriod($user, $period)
            ->mapWithKeys(fn (Budget $b) => [$b->category_id ?? 'total' => (float) $b->amount])
            ->all();

        $suggested = empty($budgets) ? Budget::convertedSuggestion($user, $period) : [];

        $lastSpent = (new PeriodSummary($user, $period->previous()))->rows
            ->mapWithKeys(fn ($row) => [$row['category']->id => $row['spent']]);

        return view('budgets.set', [
            'period' => $period,
            'amounts' => $budgets ?: $suggested,
            'isSuggestion' => empty($budgets) && ! empty($suggested),
            'categories' => $user->categories()->active()->ordered()->get(),
            'lastSpent' => $lastSpent,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $categoryIds = $user->categories()->active()->pluck('id');

        $validated = $request->validate([
            'total_budget' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'budgets' => ['array'],
            'budgets.*' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ], [
            'total_budget.required' => 'Enter a total, even if it\'s a rough guess.',
        ]);

        $period = BudgetPeriod::current($user);
        $key = ['user_id' => $user->id, 'period' => $period->type, 'period_start' => $period->start->toDateString()];

        DB::transaction(function () use ($validated, $categoryIds, $key) {
            Budget::query()->where($key)->whereNull('category_id')->delete();
            Budget::create($key + ['category_id' => null, 'amount' => $validated['total_budget']]);

            foreach ($categoryIds as $id) {
                $amount = (float) ($validated['budgets'][$id] ?? 0);
                Budget::query()->where($key)->where('category_id', $id)->delete();

                if ($amount > 0) {
                    Budget::create($key + ['category_id' => $id, 'amount' => $amount]);
                }
            }
        });

        return redirect()->route('dashboard')->with('success', 'Budget saved.');
    }
}
