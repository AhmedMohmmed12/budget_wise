<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

final class Money
{
    /**
     * Format an amount in the given currency (defaults to the signed-in user's).
     * Uses number_format rather than intl, which the server image doesn't ship.
     */
    public static function format(float|int|string|null $amount, ?string $currency = null): string
    {
        $currency ??= Auth::user()?->currency ?: 'USD';
        [$symbol, $before] = config("budgetwise.currencies.$currency", [$currency, false]);

        $value = (float) $amount;
        $number = number_format(abs($value), 2);
        $formatted = $before ? $symbol.$number : $number.' '.$symbol;

        return ($value < 0 ? '-' : '').$formatted;
    }

    public static function symbol(?string $currency = null): string
    {
        $currency ??= Auth::user()?->currency ?: 'USD';

        return config("budgetwise.currencies.$currency.0", $currency);
    }
}
