<?php

use App\Models\Budget;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-10-12 10:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('formats money in the user\'s currency', function () {
    expect(Money::format(1234.5, 'USD'))->toBe('$1,234.50')
        ->and(Money::format(1234.5, 'EGP'))->toBe('E£1,234.50')
        ->and(Money::format(99, 'SAR'))->toBe('99.00 SAR')
        ->and(Money::format(-5, 'EUR'))->toBe('-€5.00');
});

it('shows what is left this month', function () {
    $user = User::factory()->create(['name' => 'Sara Ali', 'currency' => 'EGP']);
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 1000]);
    $user->expenses()->create(['amount' => 400, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-12']);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('Hey Sara')
        ->assertSee('E£600.00 left')
        ->assertSee('October 2026');
});

it('counts entries on the last day of the period', function () {
    Carbon::setTestNow('2026-10-31 20:00:00');
    $user = User::factory()->create();
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 100]);
    $user->expenses()->create(['amount' => 30, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-31']);

    $this->actingAs($user)->get('/dashboard')->assertSee('$70.00 left');
});

it('shows the week for weekly budgets', function () {
    $user = User::factory()->create(['budget_period' => 'weekly', 'week_starts_on' => 6]);
    Budget::create(['user_id' => $user->id, 'period' => 'weekly', 'period_start' => '2026-10-10', 'amount' => 200]);
    $category = $user->categories()->first();
    $user->expenses()->create(['amount' => 50, 'category_id' => $category->id, 'date' => '2026-10-10']);
    $user->expenses()->create(['amount' => 80, 'category_id' => $category->id, 'date' => '2026-10-09']); // last week

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('This week')
        ->assertSee('Oct 10 – Oct 16, 2026')
        ->assertSee('$150.00 left')
        ->assertSee('This month so far');
});

it('shows a past month read-only', function () {
    $user = User::factory()->create();
    $user->expenses()->create(['amount' => 42, 'category_id' => $user->categories()->first()->id, 'date' => '2026-08-15', 'note' => 'Old lunch']);

    $this->actingAs($user)->get('/dashboard?period=2026-08')
        ->assertOk()
        ->assertSee('August 2026')
        ->assertSee('Old lunch')
        ->assertSee('closed')
        ->assertDontSee('+ Add expense');
});
