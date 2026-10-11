<?php

use App\Models\RecurringEntry;
use App\Models\User;
use App\Services\RecurringPoster;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

function recurring(User $user, array $attributes = []): RecurringEntry
{
    return $user->recurringEntries()->create($attributes + [
        'kind' => 'expense',
        'category_id' => $user->categories()->where('name', 'Housing')->value('id'),
        'amount' => 700,
        'description' => 'Rent',
        'day_of_month' => 1,
        'starts_on' => '2026-01-01',
    ]);
}

it('writes a due entry once per month', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    recurring($user);

    expect(RecurringPoster::catchUp($user))->toBe(1)
        ->and(RecurringPoster::catchUp($user))->toBe(0);

    $expense = $user->expenses()->sole();
    expect($expense->date->toDateString())->toBe('2026-10-01')
        ->and($expense->note)->toBe('Rent')
        ->and($expense->recurring_entry_id)->not->toBeNull();
});

it('waits until the day comes', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    recurring($user, ['day_of_month' => 25]);

    expect(RecurringPoster::catchUp($user))->toBe(0);

    Carbon::setTestNow('2026-10-25 08:00:00');
    expect(RecurringPoster::catchUp($user))->toBe(1);
});

it('never back-fills earlier months', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    recurring($user, ['starts_on' => '2026-06-01']);

    RecurringPoster::catchUp($user);

    expect($user->expenses()->pluck('date')->map->toDateString()->all())->toBe(['2026-10-01']);
});

it('uses the last day for short months', function () {
    Carbon::setTestNow('2027-02-28 10:00:00');
    $user = User::factory()->create();
    $entry = recurring($user, ['day_of_month' => 31]);

    RecurringPoster::catchUp($user);

    expect($user->expenses()->sole()->date->toDateString())->toBe('2027-02-28')
        ->and($entry->dueDateIn(Carbon::parse('2026-04-01'))->toDateString())->toBe('2026-04-30');
});

it('respects pausing, start and end dates', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    recurring($user, ['paused_at' => now()]);
    recurring($user, ['starts_on' => '2026-11-01', 'description' => 'Future']);
    recurring($user, ['ends_on' => '2026-09-30', 'description' => 'Ended']);
    recurring($user, ['starts_on' => '2026-10-05', 'description' => 'Started after this month\'s day']);

    expect(RecurringPoster::catchUp($user))->toBe(0);
});

it('writes recurring income too', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    recurring($user, ['kind' => 'income', 'category_id' => null, 'description' => 'Salary', 'amount' => 3000, 'day_of_month' => 10]);

    RecurringPoster::catchUp($user);

    expect($user->incomes()->sole())->source->toBe('Salary')->date->toDateString()->toBe('2026-10-10');
});

it('catches up when the user opens the app', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    recurring($user);

    $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Rent')->assertSee('repeats');

    expect($user->expenses()->count())->toBe(1);
});

it('is managed from the recurring page', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();
    $housing = $user->categories()->where('name', 'Housing')->value('id');

    $this->actingAs($user)->post('/recurring', [
        'kind' => 'expense', 'description' => 'Gym', 'amount' => 30, 'category_id' => $housing,
        'day_of_month' => 20, 'starts_on' => '2026-10-01',
    ])->assertRedirect('/recurring');

    $entry = $user->recurringEntries()->sole();
    $this->actingAs($user)->get('/recurring')->assertSee('Gym')->assertSee('Oct 20');
    $this->actingAs($user)->get('/dashboard')->assertSee('Coming up this month')->assertSee('Gym');

    $this->actingAs($user)->post("/recurring/{$entry->id}/pause");
    expect($entry->fresh()->isPaused())->toBeTrue();

    $this->actingAs($user)->delete("/recurring/{$entry->id}")->assertRedirect('/recurring');
    expect(RecurringEntry::count())->toBe(0);
});

it('requires a category for recurring expenses but not income', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/recurring', [
        'kind' => 'expense', 'description' => 'Gym', 'amount' => 30, 'day_of_month' => 20, 'starts_on' => '2026-10-01',
    ])->assertSessionHasErrors('category_id');

    $this->actingAs($user)->post('/recurring', [
        'kind' => 'income', 'description' => 'Salary', 'amount' => 3000, 'day_of_month' => 25, 'starts_on' => '2026-10-01',
    ])->assertSessionHasNoErrors();
});

it('keeps other users out of recurring entries', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $owner = User::factory()->create();
    $entry = recurring($owner);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get("/recurring/{$entry->id}/edit")->assertNotFound();
    $this->actingAs($intruder)->post("/recurring/{$entry->id}/pause")->assertNotFound();
    $this->actingAs($intruder)->delete("/recurring/{$entry->id}")->assertNotFound();
});

it('posts for everyone from the scheduled command', function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    recurring(User::factory()->create());
    recurring(User::factory()->create());

    $this->artisan('budgetwise:post-recurring')->expectsOutput('Wrote down 2 recurring entries.')->assertSuccessful();
});
