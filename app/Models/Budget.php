<?php

namespace App\Models;

use App\Models\Concerns\StoresPlainDates;
use App\Support\BudgetPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * A spending limit for one budget period. A null category_id is the total.
 */
class Budget extends Model
{
    use HasFactory, StoresPlainDates;

    /** @var list<string> */
    protected array $plainDates = ['period_start'];

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'period',
        'period_start',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Budgets for a period. The current period inherits the latest earlier
     * set of the same type the first time it's asked for.
     */
    public static function forPeriod(User $user, BudgetPeriod $period): Collection
    {
        $rows = static::rowsFor($user, $period);

        if ($rows->isEmpty() && $period->isCurrent($user)) {
            $latest = static::where('user_id', $user->id)
                ->where('period', $period->type)
                ->where('period_start', '<', $period->start->toDateString())
                ->max('period_start');

            if ($latest) {
                $now = now();
                $copies = static::where('user_id', $user->id)
                    ->where('period', $period->type)
                    ->whereDate('period_start', $latest)
                    ->get()
                    ->map(fn (Budget $b) => [
                        'user_id' => $user->id,
                        'category_id' => $b->category_id,
                        'amount' => $b->amount,
                        'period' => $period->type,
                        'period_start' => $period->start->toDateString(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                DB::table('budgets')->insertOrIgnore($copies->all());
                $rows = static::rowsFor($user, $period);
            }
        }

        return $rows;
    }

    /**
     * When the user switches between weekly and monthly, offer their latest
     * budget converted to the new period length. Returns [category_id|'total' => amount].
     *
     * @return array<string|int, float>
     */
    public static function convertedSuggestion(User $user, BudgetPeriod $period): array
    {
        $other = $period->isMonthly() ? BudgetPeriod::WEEKLY : BudgetPeriod::MONTHLY;

        $latest = static::where('user_id', $user->id)->where('period', $other)->max('period_start');
        if (! $latest) {
            return [];
        }

        $factor = (float) config('budgetwise.weeks_per_month');
        $factor = $period->isMonthly() ? $factor : 1 / $factor;

        return static::where('user_id', $user->id)
            ->where('period', $other)
            ->whereDate('period_start', $latest)
            ->get()
            ->mapWithKeys(fn (Budget $b) => [$b->category_id ?? 'total' => round((float) $b->amount * $factor, 2)])
            ->all();
    }

    private static function rowsFor(User $user, BudgetPeriod $period): Collection
    {
        return static::where('user_id', $user->id)
            ->where('period', $period->type)
            ->whereDate('period_start', $period->start->toDateString())
            ->get();
    }
}
