<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\BudgetThresholdReached;
use App\Support\BudgetPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Emails the user the first time the total or a category reaches 80% or
 * 100% of its budget in the current period. Each crossing is sent once.
 */
class BudgetAlerts
{
    public const THRESHOLDS = [100, 80];

    public static function check(User $user): void
    {
        if (! $user->email_alerts) {
            return;
        }

        $period = BudgetPeriod::current($user);
        $summary = new PeriodSummary($user, $period);

        $candidates = [];
        if ($summary->hasBudget()) {
            $candidates[] = ['scope' => 'total', 'name' => null, 'percentage' => $summary->spentPercentage,
                'spent' => $summary->totalSpent, 'budget' => $summary->totalBudget];
        }
        foreach ($summary->rows as $row) {
            if ($row['budget'] > 0) {
                $candidates[] = ['scope' => 'category:'.$row['category']->id, 'name' => $row['category']->name,
                    'percentage' => $row['percentage'], 'spent' => $row['spent'], 'budget' => $row['budget']];
            }
        }

        $crossed = [];
        foreach ($candidates as $candidate) {
            $reached = collect(self::THRESHOLDS)->first(fn ($t) => $candidate['percentage'] >= $t);
            if (! $reached) {
                continue;
            }

            // Record every threshold up to the one reached, so jumping straight
            // past 100% doesn't send a late 80% email afterwards.
            $rows = collect(self::THRESHOLDS)->filter(fn ($t) => $t <= $reached)->map(fn ($t) => [
                'user_id' => $user->id,
                'period' => $period->type,
                'period_start' => $period->start->toDateString(),
                'scope' => $candidate['scope'],
                'threshold' => $t,
                'sent_at' => now(),
            ]);

            $alreadySent = DB::table('budget_alerts')
                ->where(['user_id' => $user->id, 'period' => $period->type, 'period_start' => $period->start->toDateString(),
                    'scope' => $candidate['scope'], 'threshold' => $reached])
                ->exists();

            if (! $alreadySent && DB::table('budget_alerts')->insertOrIgnore($rows->values()->all()) > 0) {
                $crossed[] = $candidate + ['threshold' => $reached];
            }
        }

        if ($crossed === []) {
            return;
        }

        try {
            $user->notify(new BudgetThresholdReached($period, $crossed));
        } catch (\Throwable $e) {
            // Mail trouble shouldn't stop anyone writing down an expense; try again next time.
            Log::warning('Budget alert email failed: '.$e->getMessage(), ['user' => $user->id]);
            foreach ($crossed as $c) {
                DB::table('budget_alerts')->where(['user_id' => $user->id, 'period' => $period->type,
                    'period_start' => $period->start->toDateString(), 'scope' => $c['scope']])->delete();
            }
        }
    }
}
