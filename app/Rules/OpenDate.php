<?php

namespace App\Rules;

use App\Models\User;
use App\Support\EntryWindow;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The date must fall inside the open entry window (see EntryWindow).
 */
class OpenDate implements ValidationRule
{
    public function __construct(private User $user) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $day = CarbonImmutable::parse($value)->toDateString();
        } catch (\Throwable) {
            return; // the "date" rule reports unparsable values
        }

        [$min, $max] = EntryWindow::openRange($this->user);

        if ($day > $max) {
            $fail("Entries can't be dated in the future.");
        } elseif ($day < $min) {
            $fail('That month is closed. You can write entries dated '
                .CarbonImmutable::parse($min)->format('M j').' to today.');
        }
    }
}
