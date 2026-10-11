<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SafeReturn;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Support\EntryWindow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    use SafeReturn;

    public function store(ExpenseRequest $request): JsonResponse|RedirectResponse
    {
        $expense = $request->user()->expenses()->create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'expense' => $expense], 201);
        }

        return back()->with('success', 'Added.');
    }

    public function edit(Request $request, Expense $expense): View
    {
        $this->authorize('update', $expense);

        $user = $request->user();

        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => $user->categories()->ordered()
                ->where(fn ($q) => $q->whereNull('archived_at')->orWhere('id', $expense->category_id))
                ->get(),
            'openRange' => EntryWindow::openRange($user),
            'returnTo' => $this->safeReturn($request->query('return')),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $expense->update($request->validated());

        return redirect($this->safeReturn($request->input('return')))->with('success', 'Saved.');
    }

    public function destroy(Request $request, Expense $expense): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect($this->safeReturn($request->input('return')))->with('success', 'Moved to Recently deleted.');
    }
}
