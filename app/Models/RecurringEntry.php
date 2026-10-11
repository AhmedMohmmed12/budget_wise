<?php

namespace App\Models;

use App\Models\Concerns\StoresPlainDates;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An expense or income that's written down automatically once a month.
 */
class RecurringEntry extends Model
{
    use StoresPlainDates;

    public const EXPENSE = 'expense';

    public const INCOME = 'income';

    /** @var list<string> */
    protected array $plainDates = ['starts_on', 'ends_on', 'last_posted_month'];

    protected $fillable = [
        'user_id',
        'kind',
        'category_id',
        'amount',
        'description',
        'day_of_month',
        'starts_on',
        'ends_on',
        'paused_at',
        'last_posted_month',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'day_of_month' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'paused_at' => 'datetime',
        'last_posted_month' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function isExpense(): bool
    {
        return $this->kind === self::EXPENSE;
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    /**
     * The day it falls on in a given month (the 31st becomes the 30th, 28th…).
     */
    public function dueDateIn(CarbonInterface $month): CarbonImmutable
    {
        $first = CarbonImmutable::parse($month->format('Y-m-01'));

        return $first->day(min($this->day_of_month, $first->daysInMonth));
    }

    /**
     * Whether it should be written down in the given month at all.
     */
    public function runsIn(CarbonInterface $month): bool
    {
        if ($this->isPaused()) {
            return false;
        }

        $due = $this->dueDateIn($month)->toDateString();

        return $this->starts_on->toDateString() <= $due
            && ($this->ends_on === null || $this->ends_on->toDateString() >= $due);
    }

    public function postedIn(CarbonInterface $month): bool
    {
        return $this->last_posted_month !== null
            && $this->last_posted_month->format('Y-m') >= $month->format('Y-m');
    }

    /**
     * The next date it will be written down, or null if it has ended.
     */
    public function nextDate(CarbonInterface $today): ?CarbonImmutable
    {
        $month = CarbonImmutable::parse($today->format('Y-m-01'));

        for ($i = 0; $i < 24; $i++, $month = $month->addMonthNoOverflow()) {
            if ($this->ends_on && $this->ends_on->lt($this->dueDateIn($month))) {
                return null;
            }
            if (! $this->postedIn($month) && $this->runsIn($month)) {
                return $this->dueDateIn($month);
            }
        }

        return null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('paused_at');
    }
}
