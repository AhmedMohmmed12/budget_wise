<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Decides which dates entries may be written on: the current month up to
 * today, plus last month during the first few days of a new month.
 */
final class EntryWindow
{
    /**
     * Today's date in the user's timezone, as a plain date.
     */
    public static function today(?User $user = null): CarbonImmutable
    {
        $timezone = $user?->timezone ?: config('app.timezone');

        return CarbonImmutable::parse(CarbonImmutable::now($timezone)->toDateString());
    }

    /**
     * First date that is still open for writing.
     */
    public static function opensOn(User $user): CarbonImmutable
    {
        $today = self::today($user);

        return $today->day <= config('budgetwise.grace_days', 3)
            ? $today->subMonthNoOverflow()->startOfMonth()
            : $today->startOfMonth();
    }

    public static function isOpen(CarbonInterface|string $date, User $user): bool
    {
        [$min, $max] = self::openRange($user);
        $day = CarbonImmutable::parse($date)->toDateString();

        return $day >= $min && $day <= $max;
    }

    /**
     * @return array{0: string, 1: string} [first open date, today] as Y-m-d
     */
    public static function openRange(User $user): array
    {
        return [self::opensOn($user)->toDateString(), self::today($user)->toDateString()];
    }

    /**
     * True while last month can still be edited.
     */
    public static function inGrace(User $user): bool
    {
        return self::opensOn($user)->month !== self::today($user)->month;
    }
}
