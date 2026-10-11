<?php

use App\Models\Budget;
use App\Models\User;
use App\Notifications\BudgetThresholdReached;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    Notification::fake();
});
afterEach(fn () => Carbon::setTestNow());

function spendJson(User $user, float $amount, string $category = 'Others')
{
    return test()->actingAs($user)->postJson('/expenses', [
        'amount' => $amount,
        'category_id' => $user->categories()->where('name', $category)->value('id'),
        'date' => '2026-10-10',
    ]);
}

it('emails once at 80% and once at 100%', function () {
    $user = User::factory()->create();
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 100]);

    spendJson($user, 50);
    Notification::assertNothingSent();

    spendJson($user, 35); // 85%
    spendJson($user, 5);  // 90%, already told
    Notification::assertSentToTimes($user, BudgetThresholdReached::class, 1);

    spendJson($user, 20); // 110%
    Notification::assertSentToTimes($user, BudgetThresholdReached::class, 2);
    Notification::assertSentTo($user, BudgetThresholdReached::class,
        fn ($n) => $n->crossed[0]['threshold'] === 100 && $n->crossed[0]['scope'] === 'total');
});

it('skips straight to 100% without a late 80% email', function () {
    $user = User::factory()->create();
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 100]);

    spendJson($user, 150);
    spendJson($user, 1);

    Notification::assertSentToTimes($user, BudgetThresholdReached::class, 1);
});

it('alerts per category', function () {
    $user = User::factory()->create();
    $food = $user->categories()->where('name', 'Restaurants')->value('id');
    Budget::create(['user_id' => $user->id, 'category_id' => $food, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 50]);

    spendJson($user, 45, 'Restaurants');

    Notification::assertSentTo($user, BudgetThresholdReached::class,
        fn ($n) => $n->crossed[0]['name'] === 'Restaurants' && $n->crossed[0]['threshold'] === 80);
});

it('alerts once per week for weekly budgets', function () {
    $user = User::factory()->create(['budget_period' => 'weekly', 'week_starts_on' => 6]);
    Budget::create(['user_id' => $user->id, 'period' => 'weekly', 'period_start' => '2026-10-10', 'amount' => 100]);

    spendJson($user, 90);
    Notification::assertSentToTimes($user, BudgetThresholdReached::class, 1);

    Carbon::setTestNow('2026-10-17 10:00:00'); // next week, budget rolls over
    $this->actingAs($user)->postJson('/expenses', [
        'amount' => 85, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-17',
    ]);
    Notification::assertSentToTimes($user, BudgetThresholdReached::class, 2);
});

it('respects the email alerts setting', function () {
    $user = User::factory()->create(['email_alerts' => false]);
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 100]);

    spendJson($user, 150);

    Notification::assertNothingSent();
});

it('renders a readable email', function () {
    $user = User::factory()->create(['name' => 'Sara Ali', 'currency' => 'EGP']);
    $period = \App\Support\BudgetPeriod::current($user);
    $mail = (new BudgetThresholdReached($period, [
        ['scope' => 'total', 'name' => null, 'percentage' => 104.2, 'spent' => 2084, 'budget' => 2000, 'threshold' => 100],
    ]))->toMail($user);

    expect($mail->subject)->toBe("You've reached your October budget")
        ->and($mail->greeting)->toBe('Hi Sara,')
        ->and($mail->introLines)->toContain('Total: E£2,084.00 of E£2,000.00 (104%)');
});
