<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SafeReturn;
use App\Http\Requests\IncomeRequest;
use App\Models\Income;
use App\Support\EntryWindow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeController extends Controller
{
    use SafeReturn;

    public function store(IncomeRequest $request): JsonResponse|RedirectResponse
    {
        $income = $request->user()->incomes()->create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'income' => $income], 201);
        }

        return back()->with('success', 'Added.');
    }

    public function edit(Request $request, Income $income): View
    {
        $this->authorize('update', $income);

        return view('incomes.edit', [
            'income' => $income,
            'openRange' => EntryWindow::openRange($request->user()),
            'returnTo' => $this->safeReturn($request->query('return')),
        ]);
    }

    public function update(IncomeRequest $request, Income $income): RedirectResponse
    {
        $this->authorize('update', $income);

        $income->update($request->validated());

        return redirect($this->safeReturn($request->input('return')))->with('success', 'Saved.');
    }

    public function destroy(Request $request, Income $income): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $income);

        $income->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect($this->safeReturn($request->input('return')))->with('success', 'Moved to Recently deleted.');
    }
}
