<?php

namespace App\Models;

use App\Models\Concerns\StoresPlainDates;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use StoresPlainDates;

    /** @var list<string> */
    protected array $plainDates = ['target_date'];

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'target_date',
        'completed_at',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'target_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }

    public function isDone(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Total put aside so far (uses a withSum() value when it was eager loaded).
     */
    public function savedAmount(): float
    {
        return (float) ($this->contributions_sum_amount ?? $this->contributions()->sum('amount'));
    }

    public function remaining(): float
    {
        return max(0, (float) $this->target_amount - $this->savedAmount());
    }

    public function percentage(): float
    {
        return $this->target_amount > 0 ? min(100, $this->savedAmount() / (float) $this->target_amount * 100) : 0;
    }

    public function isReached(): bool
    {
        return $this->savedAmount() >= (float) $this->target_amount;
    }

    /**
     * Average put aside per month over the last three months.
     */
    public function monthlyPace(CarbonInterface $today): float
    {
        $from = CarbonImmutable::parse($today->format('Y-m-01'))->subMonthsNoOverflow(2);

        return (float) $this->contributions()->where('date', '>=', $from->toDateString())->sum('amount') / 3;
    }

    /**
     * When the goal will be reached at the recent pace, or null if it won't.
     */
    public function projectedDate(CarbonInterface $today): ?CarbonImmutable
    {
        if ($this->isReached()) {
            return null;
        }

        $pace = $this->monthlyPace($today);
        if ($pace <= 0) {
            return null;
        }

        $months = (int) ceil($this->remaining() / $pace);

        return $months > 600 ? null : CarbonImmutable::parse($today->format('Y-m-01'))->addMonthsNoOverflow($months);
    }

    /**
     * How much a month it takes to hit the target date from here.
     */
    public function neededPerMonth(CarbonInterface $today): ?float
    {
        if (! $this->target_date || $this->isReached()) {
            return null;
        }

        $months = max(1, (int) CarbonImmutable::parse($today->format('Y-m-01'))
            ->diffInMonths(CarbonImmutable::parse($this->target_date->format('Y-m-01'))) + 1);

        return $this->remaining() / $months;
    }
}
