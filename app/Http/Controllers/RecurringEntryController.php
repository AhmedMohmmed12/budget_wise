<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringEntryRequest;
use App\Models\RecurringEntry;
use App\Services\RecurringPoster;
use App\Support\EntryWindow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringEntryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('recurring.index', [
            'entries' => $user->recurringEntries()->with('category')
                ->orderByRaw('paused_at is not null')->orderBy('kind')->orderBy('day_of_month')->get(),
            'categories' => $user->categories()->active()->ordered()->get(),
            'today' => EntryWindow::today($user),
        ]);
    }

    public function store(RecurringEntryRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->recurringEntries()->create($request->entryData());

        $posted = RecurringPoster::catchUp($user);

        return redirect()->route('recurring.index')->with('success', $posted
            ? 'Saved, and this month\'s one is already written down.'
            : 'Saved. It\'ll be written down automatically on the day.');
    }

    public function edit(Request $request, RecurringEntry $recurring): View
    {
        $this->authorize('update', $recurring);

        return view('recurring.edit', [
            'entry' => $recurring,
            'categories' => $request->user()->categories()->ordered()
                ->where(fn ($q) => $q->whereNull('archived_at')->orWhere('id', $recurring->category_id))
                ->get(),
        ]);
    }

    public function update(RecurringEntryRequest $request, RecurringEntry $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);

        $recurring->update($request->entryData());
        RecurringPoster::catchUp($request->user());

        return redirect()->route('recurring.index')->with('success', 'Saved. Entries already written down are left as they are.');
    }

    public function pause(Request $request, RecurringEntry $recurring): RedirectResponse
    {
        $this->authorize('update', $recurring);

        $recurring->update(['paused_at' => $recurring->isPaused() ? null : now()]);

        if (! $recurring->isPaused()) {
            RecurringPoster::catchUp($request->user());
        }

        return back()->with('success', $recurring->isPaused() ? "Paused {$recurring->description}." : "Resumed {$recurring->description}.");
    }

    public function destroy(RecurringEntry $recurring): RedirectResponse
    {
        $this->authorize('delete', $recurring);

        $recurring->delete();

        return redirect()->route('recurring.index')
            ->with('success', "Stopped {$recurring->description}. Entries already written down stay.");
    }
}
