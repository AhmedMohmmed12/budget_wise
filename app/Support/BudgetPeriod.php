<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * One budget period: a calendar month, or a 7-day week starting on the
 * user's chosen weekday.
 */
final class BudgetPeriod
{
    public const MONTHLY = 'monthly';

    public const WEEKLY = 'weekly';

    private function __construct(
        public readonly string $type,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly int $weekStartsOn,
    ) {}

    public static function make(string $type, CarbonInterface|string $date, int $weekStartsOn = CarbonInterface::SATURDAY): self
    {
        $day = CarbonImmutable::parse(CarbonImmutable::parse($date)->toDateString());

        if ($type === self::WEEKLY) {
            $start = $day->startOfWeek($weekStartsOn);

            return new self(self::WEEKLY, $start, $start->addDays(6), $weekStartsOn);
        }

        return new self(self::MONTHLY, $day->startOfMonth(), $day->endOfMonth()->startOfDay(), $weekStartsOn);
    }

    public static function containing(User $user, CarbonInterface|string $date): self
    {
        return self::make($user->budget_period ?: self::MONTHLY, $date, (int) $user->week_starts_on);
    }

    public static function current(User $user): self
    {
        return self::containing($user, EntryWindow::today($user));
    }

    /**
     * Build from a URL key ("2026-10" or "2026-10-10"); anything invalid or in
     * the future falls back to the current period.
     */
    public static function fromKey(User $user, ?string $key): self
    {
        $current = self::current($user);

        if (! $key) {
            return $current;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $key)) {
            $key .= '-01';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $key) || ! strtotime($key)) {
            return $current;
        }

        $period = self::containing($user, $key);

        return $period->start->greaterThan($current->start) ? $current : $period;
    }

    public function key(): string
    {
        return $this->isMonthly() ? $this->start->format('Y-m') : $this->start->toDateString();
    }

    public function isMonthly(): bool
    {
        return $this->type === self::MONTHLY;
    }

    public function previous(): self
    {
        return self::make($this->type, $this->start->subDay(), $this->weekStartsOn);
    }

    public function next(): self
    {
        return self::make($this->type, $this->end->addDay(), $this->weekStartsOn);
    }

    public function contains(CarbonInterface|string $date): bool
    {
        $day = CarbonImmutable::parse($date)->toDateString();

        return $day >= $this->start->toDateString() && $day <= $this->end->toDateString();
    }

    public function isCurrent(User $user): bool
    {
        return $this->contains(EntryWindow::today($user));
    }

    public function days(): int
    {
        return (int) $this->start->diffInDays($this->end) + 1;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function range(): array
    {
        return [$this->start->toDateString(), $this->end->toDateString()];
    }

    public function label(): string
    {
        if ($this->isMonthly()) {
            return $this->start->format('F Y');
        }

        $format = $this->start->year === $this->end->year ? 'M j' : 'M j, Y';

        return $this->start->format($format).' – '.$this->end->format('M j, Y');
    }

    /** "month" or "week", for copy. */
    public function noun(): string
    {
        return $this->isMonthly() ? 'month' : 'week';
    }
}
