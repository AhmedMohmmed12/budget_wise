<?php

use App\Models\User;
use App\Support\BudgetPeriod;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('builds weeks from the chosen first weekday', function (int $startsOn, string $start, string $end) {
    $week = BudgetPeriod::make(BudgetPeriod::WEEKLY, '2026-10-12', $startsOn); // a Monday

    expect($week->start->toDateString())->toBe($start)
        ->and($week->end->toDateString())->toBe($end)
        ->and($week->days())->toBe(7);
})->with([
    'saturday' => [6, '2026-10-10', '2026-10-16'],
    'sunday' => [0, '2026-10-11', '2026-10-17'],
    'monday' => [1, '2026-10-12', '2026-10-18'],
]);

it('handles weeks that cross a month or a year', function () {
    $acrossMonth = BudgetPeriod::make(BudgetPeriod::WEEKLY, '2026-11-02', 6);
    $acrossYear = BudgetPeriod::make(BudgetPeriod::WEEKLY, '2026-12-31', 6);

    expect($acrossMonth->range())->toBe(['2026-10-31', '2026-11-06'])
        ->and($acrossYear->range())->toBe(['2026-12-26', '2027-01-01'])
        ->and($acrossYear->next()->range())->toBe(['2027-01-02', '2027-01-08']);
});

it('covers whole calendar months', function () {
    $feb = BudgetPeriod::make(BudgetPeriod::MONTHLY, '2028-02-14');

    expect($feb->range())->toBe(['2028-02-01', '2028-02-29'])
        ->and($feb->previous()->range())->toBe(['2028-01-01', '2028-01-31'])
        ->and($feb->key())->toBe('2028-02')
        ->and($feb->label())->toBe('February 2028');
});

it('reads periods from URL keys and never goes past the current one', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $monthly = User::factory()->create(['budget_period' => 'monthly']);
    $weekly = User::factory()->create(['budget_period' => 'weekly', 'week_starts_on' => 6]);

    expect(BudgetPeriod::fromKey($monthly, '2026-08')->key())->toBe('2026-08')
        ->and(BudgetPeriod::fromKey($monthly, '2027-01')->key())->toBe('2026-10')
        ->and(BudgetPeriod::fromKey($monthly, 'nonsense')->key())->toBe('2026-10')
        ->and(BudgetPeriod::fromKey($weekly, '2026-10-01')->key())->toBe('2026-09-26')
        ->and(BudgetPeriod::fromKey($weekly, null)->key())->toBe('2026-10-10');
});
