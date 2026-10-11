<?php

use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-10-12 10:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('records income with the same date rules as expenses', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/income', ['amount' => 3000, 'source' => 'Salary', 'date' => '2026-10-01'])->assertCreated();
    $this->actingAs($user)->postJson('/income', ['amount' => 50, 'source' => 'Gift', 'date' => '2026-09-25'])
        ->assertJsonValidationErrors('date');
    $this->actingAs($user)->postJson('/income', ['amount' => 50, 'date' => '2026-10-02'])
        ->assertJsonValidationErrors('source');

    expect($user->incomes()->count())->toBe(1);
});

it('shows the month\'s money in, out and kept on the dashboard', function () {
    $user = User::factory()->create();
    $user->incomes()->create(['amount' => 3000, 'source' => 'Salary', 'date' => '2026-10-01']);
    $user->expenses()->create(['amount' => 1200, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-03']);

    $this->actingAs($user)->get('/dashboard')
        ->assertSee('October money')
        ->assertSee('+$3,000.00')
        ->assertSee('$1,800.00');
});

it('edits and deletes income in an open month only', function () {
    $user = User::factory()->create();
    $open = $user->incomes()->create(['amount' => 10, 'source' => 'Refund', 'date' => '2026-10-02']);
    $closed = $user->incomes()->create(['amount' => 10, 'source' => 'Old', 'date' => '2026-09-02']);

    $this->actingAs($user)->patch("/income/{$open->id}", ['amount' => 15, 'source' => 'Refund', 'date' => '2026-10-02'])->assertRedirect();
    $this->actingAs($user)->patch("/income/{$closed->id}", ['amount' => 15, 'source' => 'Old', 'date' => '2026-10-02'])->assertForbidden();
    $this->actingAs($user)->deleteJson("/income/{$open->id}")->assertOk();

    expect(Income::withTrashed()->find($open->id)->trashed())->toBeTrue()
        ->and((float) $closed->fresh()->amount)->toBe(10.0);
});

it('lists income on its own tab and exports it', function () {
    $user = User::factory()->create();
    $user->incomes()->create(['amount' => 3000, 'source' => 'Salary', 'date' => '2026-10-01', 'note' => 'October pay']);
    $user->expenses()->create(['amount' => 5, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-02', 'note' => 'Coffee']);

    $this->actingAs($user)->get('/entries?type=income')
        ->assertSee('Salary')
        ->assertDontSee('Coffee');

    $csv = $this->actingAs($user)->get('/entries/export?type=income&month=2026-10')->streamedContent();
    expect($csv)->toContain('Date,Source,Note,Amount,Currency')->toContain('2026-10-01,Salary,"October pay",3000.00,USD');
});

it('restores deleted income from the trash', function () {
    $user = User::factory()->create();
    $income = $user->incomes()->create(['amount' => 20, 'source' => 'Side job', 'date' => '2026-10-05']);
    $income->delete();

    $this->actingAs($user)->get('/entries/trash')->assertSee('Side job');
    $this->actingAs($user)->post("/entries/trash/income/{$income->id}/restore")->assertRedirect();

    expect($income->fresh()->trashed())->toBeFalse();
});

it('keeps other users out of income', function () {
    $owner = User::factory()->create();
    $income = $owner->incomes()->create(['amount' => 20, 'source' => 'Mine', 'date' => '2026-10-05']);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get("/income/{$income->id}/edit")->assertNotFound();
    $this->actingAs($intruder)->deleteJson("/income/{$income->id}")->assertNotFound();
    $this->actingAs($intruder)->get('/entries?type=income&month=all')->assertDontSee('Mine');
});
