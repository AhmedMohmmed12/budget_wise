<?php

use App\Models\Budget;
use App\Models\GoalContribution;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-10-12 10:00:00'));
afterEach(fn () => Carbon::setTestNow());

function goal(User $user, array $attributes = []): SavingsGoal
{
    return $user->savingsGoals()->create($attributes + ['name' => 'New laptop', 'target_amount' => 1200]);
}

it('creates a goal and tracks money put in and taken out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/goals', ['name' => 'Trip', 'target_amount' => 900, 'target_date' => '2027-06-01'])
        ->assertSessionHasNoErrors();
    $goal = $user->savingsGoals()->sole();

    $this->actingAs($user)->post("/goals/{$goal->id}/contributions", ['amount' => 300, 'direction' => 'in', 'date' => '2026-10-05']);
    $this->actingAs($user)->post("/goals/{$goal->id}/contributions", ['amount' => 50, 'direction' => 'out', 'date' => '2026-10-06']);

    expect($goal->savedAmount())->toBe(250.0)
        ->and($goal->remaining())->toBe(650.0);

    $this->actingAs($user)->get('/goals')->assertOk()->assertSee('Trip')->assertSee('$650.00 to go');
});

it('will not take out more than is in the goal', function () {
    $user = User::factory()->create();
    $goal = goal($user);

    $this->actingAs($user)->post("/goals/{$goal->id}/contributions", ['amount' => 10, 'direction' => 'out', 'date' => '2026-10-05'])
        ->assertSessionHasErrorsIn("goal{$goal->id}", 'amount');
});

it('applies the open-month rule to contributions', function () {
    $user = User::factory()->create();
    $goal = goal($user);

    $this->actingAs($user)->post("/goals/{$goal->id}/contributions", ['amount' => 10, 'direction' => 'in', 'date' => '2026-09-05'])
        ->assertSessionHasErrorsIn("goal{$goal->id}", 'date');

    $old = $goal->contributions()->create(['user_id' => $user->id, 'amount' => 10, 'date' => '2026-09-05']);
    $this->actingAs($user)->delete("/goal-contributions/{$old->id}")->assertForbidden();
});

it('projects when a goal will be reached from the recent pace', function () {
    $user = User::factory()->create();
    $goal = goal($user, ['target_amount' => 1200, 'target_date' => '2027-03-31']);
    foreach (['2026-08-10', '2026-09-10', '2026-10-10'] as $date) {
        $goal->contributions()->create(['user_id' => $user->id, 'amount' => 100, 'date' => $date]);
    }

    $today = Carbon::parse('2026-10-12');

    expect($goal->monthlyPace($today))->toBe(100.0)
        ->and($goal->projectedDate($today)->format('Y-m'))->toBe('2027-07')
        ->and($goal->neededPerMonth($today))->toBe(150.0);
});

it('counts contributions as set aside, not spending', function () {
    $user = User::factory()->create();
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 1000]);
    $user->incomes()->create(['amount' => 2000, 'source' => 'Salary', 'date' => '2026-10-01']);
    goal($user)->contributions()->create(['user_id' => $user->id, 'amount' => 300, 'date' => '2026-10-02']);

    $this->actingAs($user)->get('/dashboard')
        ->assertSee('$1,000.00 left')
        ->assertSee('Set aside')
        ->assertSee('$1,700.00')
        ->assertSee('New laptop');
});

it('marks goals done and keeps other users out', function () {
    $user = User::factory()->create();
    $goal = goal($user);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->post("/goals/{$goal->id}/complete")->assertNotFound();
    $this->actingAs($intruder)->post("/goals/{$goal->id}/contributions", ['amount' => 1, 'direction' => 'in', 'date' => '2026-10-01'])->assertNotFound();

    $this->actingAs($user)->post("/goals/{$goal->id}/complete");
    expect($goal->fresh()->isDone())->toBeTrue();

    $this->actingAs($user)->delete("/goals/{$goal->id}")->assertRedirect('/goals');
    expect(SavingsGoal::count())->toBe(0)->and(GoalContribution::count())->toBe(0);
});

it('reports each month of the year with budgets carried forward', function () {
    $user = User::factory()->create();
    $category = $user->categories()->first()->id;
    Budget::create(['user_id' => $user->id, 'period' => 'monthly', 'period_start' => '2026-08-01', 'amount' => 500]);
    $user->expenses()->create(['amount' => 600, 'category_id' => $category, 'date' => '2026-08-15']);
    $user->expenses()->create(['amount' => 200, 'category_id' => $category, 'date' => '2026-09-15']);
    $user->incomes()->create(['amount' => 1000, 'source' => 'Salary', 'date' => '2026-09-01']);
    $deleted = $user->expenses()->create(['amount' => 999, 'category_id' => $category, 'date' => '2026-09-16']);
    $deleted->delete();

    $response = $this->actingAs($user)->get('/reports');
    $months = $response->viewData('months')->keyBy('key');

    expect($months)->toHaveCount(10)
        ->and($months['2026-08'])->toMatchArray(['spent' => 600.0, 'budget' => 500.0])
        ->and($months['2026-09'])->toMatchArray(['spent' => 200.0, 'budget' => 500.0, 'income' => 1000.0, 'kept' => 800.0])
        ->and($months['2026-07']['budget'])->toBe(0.0);

    $response->assertSee('2026, month by month')->assertSee('over');
});

it('shows the last twelve weeks for weekly budgets', function () {
    $user = User::factory()->create(['budget_period' => 'weekly', 'week_starts_on' => 6]);
    Budget::create(['user_id' => $user->id, 'period' => 'weekly', 'period_start' => '2026-09-26', 'amount' => 200]);
    $user->expenses()->create(['amount' => 250, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-04']);

    $weeks = $this->actingAs($user)->get('/reports')->assertSee('Last 12 weeks')->viewData('weeks');

    expect($weeks)->toHaveCount(12)
        ->and($weeks[0]['period']->start->toDateString())->toBe('2026-10-10')
        ->and($weeks[1])->toMatchArray(['spent' => 250.0, 'budget' => 200.0]);
});
