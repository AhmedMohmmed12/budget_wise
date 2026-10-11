<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Support\EntryWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsGoalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $goals = $user->savingsGoals()
            ->withSum('contributions', 'amount')
            ->with(['contributions' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id')])
            ->orderByRaw('completed_at is not null')
            ->orderBy('target_date')
            ->orderBy('id')
            ->get();

        return view('goals.index', [
            'active' => $goals->reject->isDone(),
            'done' => $goals->filter->isDone(),
            'today' => EntryWindow::today($user),
            'openRange' => EntryWindow::openRange($user),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user()->savingsGoals()->create($this->validated($request));

        return back()->with('success', 'Goal added. Put something toward it whenever you can.');
    }

    public function edit(SavingsGoal $goal): View
    {
        $this->authorize('update', $goal);

        return view('goals.edit', ['goal' => $goal]);
    }

    public function update(Request $request, SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $goal->update($this->validated($request));

        return redirect()->route('goals.index')->with('success', 'Saved.');
    }

    public function complete(SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $goal->update(['completed_at' => $goal->isDone() ? null : now()]);

        return back()->with('success', $goal->isDone() ? "Nice one. {$goal->name} is done." : "{$goal->name} is open again.");
    }

    public function destroy(SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')->with('success', "Deleted {$goal->name}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:9999999999.99'],
            'target_date' => ['nullable', 'date', 'after:today'],
        ], [
            'name.required' => 'What are you saving for?',
            'target_date.after' => 'Pick a date in the future, or leave it empty.',
        ]);
    }
}
