<?php

namespace App\Http\Controllers;

use App\Models\Expense;
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
        $cutoff = now()->subDays(config('budgetwise.trash_days'));

        return view('entries.trash', [
            'expenses' => $request->user()->expenses()->onlyTrashed()
                ->with('category')
                ->where('deleted_at', '>', $cutoff)
                ->orderByDesc('deleted_at')
                ->get(),
        ]);
    }

    public function restoreExpense(Expense $expense): RedirectResponse
    {
        $this->authorize('restore', $expense);
        abort_unless($expense->trashed(), 404);

        $expense->restore();

        return back()->with('success', 'Restored.');
    }

    public function destroyExpense(Expense $expense): RedirectResponse
    {
        $this->authorize('forceDelete', $expense);
        abort_unless($expense->trashed(), 404);

        $expense->forceDelete();

        return back()->with('success', 'Deleted for good.');
    }
}
