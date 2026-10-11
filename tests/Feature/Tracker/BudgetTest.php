<?php

use App\Models\Budget;
use App\Models\User;
use App\Support\BudgetPeriod;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

function budgetSet(User $user, string $period, string $start, array $amounts): void
{
    foreach ($amounts as $categoryId => $amount) {
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $categoryId === 'total' ? null : $categoryId,
            'period' => $period,
            'period_start' => $start,
            'amount' => $amount,
        ]);
    }
}

it('carries last month\'s budget into the new month exactly once', function () {
    $user = User::factory()->create();
    $housing = $user->categories()->where('name', 'Housing')->first();
    budgetSet($user, 'monthly', '2026-09-01', ['total' => 2000, $housing->id => 700]);

    Carbon::setTestNow('2026-10-05 10:00:00');
    $period = BudgetPeriod::current($user);

    Budget::forPeriod($user, $period);
    $rows = Budget::forPeriod($user, $period);

    expect($rows)->toHaveCount(2)
        ->and(Budget::where('user_id', $user->id)->count())->toBe(4)
        ->and((float) $rows->firstWhere('category_id', null)->amount)->toBe(2000.0);
});

it('carries weekly budgets into the new week', function () {
    $user = User::factory()->create(['budget_period' => 'weekly', 'week_starts_on' => 6]);
    budgetSet($user, 'weekly', '2026-10-03', ['total' => 400]);

    Carbon::setTestNow('2026-10-12 10:00:00');

    $rows = Budget::forPeriod($user, BudgetPeriod::current($user));

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->period_start->toDateString())->toBe('2026-10-10');
});

it('does not invent budgets for past periods', function () {
    $user = User::factory()->create();
    budgetSet($user, 'monthly', '2026-10-01', ['total' => 2000]);
    Carbon::setTestNow('2026-10-12 10:00:00');

    expect(Budget::forPeriod($user, BudgetPeriod::current($user)->previous()))->toBeEmpty();
});

it('saves the budget for the current period from the form', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    $food = $user->categories()->where('name', 'Restaurants')->first();
    $fun = $user->categories()->where('name', 'Entertainment')->first();

    $this->actingAs($user)->post('/budget', [
        'total_budget' => 1500,
        'budgets' => [$food->id => 300, $fun->id => ''],
    ])->assertRedirect(route('dashboard'));

    $rows = Budget::where('user_id', $user->id)->get();
    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('period_start')->map->toDateString()->unique()->all())->toBe(['2026-10-01'])
        ->and((float) $rows->firstWhere('category_id', $food->id)->amount)->toBe(300.0);

    // Saving again replaces rather than duplicates, and clearing a limit removes it.
    $this->actingAs($user)->post('/budget', ['total_budget' => 1600, 'budgets' => [$food->id => '']]);
    expect(Budget::where('user_id', $user->id)->count())->toBe(1);
});

it('offers a converted budget after switching from monthly to weekly', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    budgetSet($user, 'monthly', '2026-10-01', ['total' => 433]);

    $this->actingAs($user)->patch('/profile/preferences', [
        'currency' => 'USD', 'timezone' => 'UTC', 'budget_period' => 'weekly', 'week_starts_on' => 6,
    ])->assertRedirect(route('budgets.index'));

    $this->actingAs($user->fresh())->get('/budget')
        ->assertOk()
        ->assertSee('converted to a week')
        ->assertSee('value="100"', false);
});
