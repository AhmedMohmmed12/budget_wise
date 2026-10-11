<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Recently deleted": soft-deleted entries kept for a while before pruning.
 */
class TrashController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $cutoff = now()->subDays(config('budgetwise.trash_days'));

        $expenses = $user->expenses()->onlyTrashed()->with('category')->where('deleted_at', '>', $cutoff)->get();
        $incomes = $user->incomes()->onlyTrashed()->where('deleted_at', '>', $cutoff)->get();

        return view('entries.trash', [
            'entries' => $expenses->concat($incomes)->sortByDesc('deleted_at')->values(),
        ]);
    }

    public function restoreExpense(Expense $expense): RedirectResponse
    {
        return $this->restore($expense);
    }

    public function destroyExpense(Expense $expense): RedirectResponse
    {
        return $this->forceDelete($expense);
    }

    public function restoreIncome(Income $income): RedirectResponse
    {
        return $this->restore($income);
    }

    public function destroyIncome(Income $income): RedirectResponse
    {
        return $this->forceDelete($income);
    }

    private function restore(Model $entry): RedirectResponse
    {
        $this->authorize('restore', $entry);
        abort_unless($entry->trashed(), 404);

        $entry->restore();

        return back()->with('success', 'Restored.');
    }

    private function forceDelete(Model $entry): RedirectResponse
    {
        $this->authorize('forceDelete', $entry);
        abort_unless($entry->trashed(), 404);

        $entry->forceDelete();

        return back()->with('success', 'Deleted for good.');
    }
}
