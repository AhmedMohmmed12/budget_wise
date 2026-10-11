<?php

namespace App\Services;

use App\Models\RecurringEntry;
use App\Models\User;
use App\Support\EntryWindow;
use Illuminate\Support\Facades\DB;

/**
 * Writes down recurring entries that are due in the current month.
 * Never posts into earlier (possibly closed) months, and posts each entry at
 * most once per month, so it's safe to call on every request.
 */
class RecurringPoster
{
    /**
     * @return int how many entries were written
     */
    public static function catchUp(User $user): int
    {
        $today = EntryWindow::today($user);
        $month = $today->startOfMonth();

        $candidates = RecurringEntry::query()
            ->where('user_id', $user->id)
            ->whereNull('paused_at')
            ->where('starts_on', '<=', $today->toDateString())
            ->where(fn ($q) => $q->whereNull('ends_on')->orWhere('ends_on', '>=', $month->toDateString()))
            ->where(fn ($q) => $q->whereNull('last_posted_month')->orWhere('last_posted_month', '<', $month->toDateString()))
            ->get();

        $posted = 0;

        foreach ($candidates as $entry) {
            $due = $entry->dueDateIn($month);

            if ($due->gt($today) || ! $entry->runsIn($month)) {
                continue;
            }

            $posted += DB::transaction(function () use ($entry, $month, $due, $user) {
                $fresh = RecurringEntry::whereKey($entry->id)->lockForUpdate()->first();

                if (! $fresh || $fresh->postedIn($month)) {
                    return 0;
                }

                if ($fresh->isExpense()) {
                    $user->expenses()->create([
                        'recurring_entry_id' => $fresh->id,
                        'category_id' => $fresh->category_id,
                        'amount' => $fresh->amount,
                        'date' => $due,
                        'note' => $fresh->description,
                    ]);
                } else {
                    $user->incomes()->create([
                        'recurring_entry_id' => $fresh->id,
                        'amount' => $fresh->amount,
                        'source' => $fresh->description,
                        'date' => $due,
                    ]);
                }

                $fresh->update(['last_posted_month' => $month]);

                return 1;
            });
        }

        return $posted;
    }
}
