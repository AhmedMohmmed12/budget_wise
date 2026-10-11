<?php

namespace App\Http\Controllers;

use App\Models\GoalContribution;
use App\Models\SavingsGoal;
use App\Rules\OpenDate;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoalContributionController extends Controller
{
    public function store(Request $request, SavingsGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $data = $request->validateWithBag("goal{$goal->id}", [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'direction' => ['required', 'in:in,out'],
            'date' => ['required', 'date', new OpenDate($request->user())],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $data['amount'];
        if ($data['direction'] === 'out') {
            if ($amount > $goal->savedAmount()) {
                return back()->withErrors(['amount' => "There's only ".Money::format($goal->savedAmount()).' in this goal.'], "goal{$goal->id}")->withInput();
            }
            $amount = -$amount;
        }

        $goal->contributions()->create([
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'date' => $data['date'],
            'note' => $data['note'] ?? null,
        ]);

        $goal->unsetRelation('contributions');

        return back()->with('success', $goal->fresh()->isReached() && ! $goal->isDone()
            ? "That's it, you've reached {$goal->name}. Mark it done when you're ready."
            : ($amount > 0 ? 'Put aside.' : 'Taken out.'));
    }

    public function destroy(GoalContribution $contribution): RedirectResponse
    {
        $this->authorize('delete', $contribution);

        $contribution->delete();

        return back()->with('success', 'Removed.');
    }
}
