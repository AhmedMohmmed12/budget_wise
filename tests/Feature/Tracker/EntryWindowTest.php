<?php

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;

function addExpense(User $user, string $date)
{
    return test()->actingAs($user)->postJson('/expenses', [
        'amount' => 10,
        'category_id' => $user->categories()->first()->id,
        'date' => $date,
    ]);
}

afterEach(fn () => Carbon::setTestNow());

it('accepts any day of the current month up to today', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();

    addExpense($user, '2026-10-01')->assertCreated();
    addExpense($user, '2026-10-12')->assertCreated();
});

it('rejects future dates', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();

    addExpense($user, '2026-10-13')
        ->assertUnprocessable()
        ->assertJsonPath('errors.date.0', "Entries can't be dated in the future.");
});

it('keeps last month open for the first three days only', function (string $today, bool $allowed) {
    Carbon::setTestNow($today);
    $user = User::factory()->create();

    $response = addExpense($user, '2026-09-20');

    $allowed ? $response->assertCreated() : $response->assertUnprocessable()->assertJsonValidationErrors('date');
})->with([
    'day 1' => ['2026-10-01 09:00:00', true],
    'day 3' => ['2026-10-03 23:00:00', true],
    'day 4' => ['2026-10-04 00:30:00', false],
    'mid month' => ['2026-10-20 12:00:00', false],
]);

it('never opens the month before last', function () {
    Carbon::setTestNow('2026-10-02 10:00:00');
    $user = User::factory()->create();

    addExpense($user, '2026-08-31')->assertUnprocessable()->assertJsonValidationErrors('date');
});

it('uses the user timezone to decide what today is', function () {
    // 12:00 UTC on Sep 30 is already Oct 1 in Kiritimati (UTC+14).
    Carbon::setTestNow('2026-09-30 12:00:00');

    $ahead = User::factory()->create(['timezone' => 'Pacific/Kiritimati']);
    $utc = User::factory()->create(['timezone' => 'UTC']);

    addExpense($ahead, '2026-10-01')->assertCreated();
    addExpense($utc, '2026-10-01')->assertUnprocessable();
});

it('refuses to edit or delete an entry once its month is closed', function () {
    $user = User::factory()->create();
    Carbon::setTestNow('2026-09-20 10:00:00');
    $expense = $user->expenses()->create([
        'amount' => 20, 'category_id' => $user->categories()->first()->id, 'date' => '2026-09-18',
    ]);

    Carbon::setTestNow('2026-10-08 10:00:00');

    $this->actingAs($user)->get("/expenses/{$expense->id}/edit")->assertForbidden();
    $this->actingAs($user)->patch("/expenses/{$expense->id}", [
        'amount' => 5, 'category_id' => $expense->category_id, 'date' => '2026-10-08',
    ])->assertForbidden();
    $this->actingAs($user)->deleteJson("/expenses/{$expense->id}")->assertForbidden();

    expect(Expense::find($expense->id))->not->toBeNull();
});

it('still allows fixing last month during the grace days', function () {
    $user = User::factory()->create();
    Carbon::setTestNow('2026-09-29 10:00:00');
    $expense = $user->expenses()->create([
        'amount' => 20, 'category_id' => $user->categories()->first()->id, 'date' => '2026-09-29',
    ]);

    Carbon::setTestNow('2026-10-02 10:00:00');

    $this->actingAs($user)->patch("/expenses/{$expense->id}", [
        'amount' => 25, 'category_id' => $expense->category_id, 'date' => '2026-09-28', 'note' => 'Fixed',
    ])->assertRedirect();

    expect($expense->fresh())
        ->amount->toEqual('25.00')
        ->note->toBe('Fixed')
        ->date->toDateString()->toBe('2026-09-28');
});
