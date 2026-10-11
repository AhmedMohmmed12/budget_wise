<?php

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-10-12 10:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('gives new users the default categories', function () {
    $user = User::factory()->create();

    expect($user->categories()->ordered()->pluck('name')->all())
        ->toBe(array_keys(config('budgetwise.default_categories')));
});

it('stores an expense against one of the user\'s categories', function () {
    $user = User::factory()->create();
    $category = $user->categories()->where('name', 'Housing')->first();

    $this->actingAs($user)->postJson('/expenses', [
        'amount' => '650', 'category_id' => $category->id, 'date' => '2026-10-01', 'note' => 'Rent',
    ])->assertCreated();

    expect($user->expenses()->first())
        ->category_id->toBe($category->id)
        ->amount->toEqual('650.00');
});

it('rejects another user\'s category and archived categories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $archived = $user->categories()->first();
    $archived->update(['archived_at' => now()]);

    foreach ([$other->categories()->first()->id, $archived->id] as $categoryId) {
        $this->actingAs($user)->postJson('/expenses', [
            'amount' => 5, 'category_id' => $categoryId, 'date' => '2026-10-10',
        ])->assertJsonValidationErrors('category_id');
    }
});

it('hides other users\' expenses', function () {
    $owner = User::factory()->create();
    $expense = $owner->expenses()->create([
        'amount' => 9, 'category_id' => $owner->categories()->first()->id, 'date' => '2026-10-10',
    ]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get("/expenses/{$expense->id}/edit")->assertNotFound();
    $this->actingAs($intruder)->deleteJson("/expenses/{$expense->id}")->assertNotFound();
    $this->actingAs($intruder)->patch("/expenses/{$expense->id}", [
        'amount' => 1, 'category_id' => $intruder->categories()->first()->id, 'date' => '2026-10-10',
    ])->assertNotFound();
});

it('edits an open expense and returns to where the user came from', function () {
    $user = User::factory()->create();
    $expense = $user->expenses()->create([
        'amount' => 9, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-10',
    ]);
    $newCategory = $user->categories()->where('name', 'Shopping')->first();

    $this->actingAs($user)->get("/expenses/{$expense->id}/edit")->assertOk()->assertSee('Edit entry');

    $this->actingAs($user)->patch("/expenses/{$expense->id}", [
        'amount' => 12.5, 'category_id' => $newCategory->id, 'date' => '2026-10-11',
        'return' => url('/dashboard?period=2026-10'),
    ])->assertRedirect(url('/dashboard?period=2026-10'));

    expect($expense->fresh())->category_id->toBe($newCategory->id)->amount->toEqual('12.50');
});

it('ignores return links that leave the app', function () {
    $user = User::factory()->create();
    $expense = $user->expenses()->create([
        'amount' => 9, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-10',
    ]);

    $this->actingAs($user)->patch("/expenses/{$expense->id}", [
        'amount' => 9, 'category_id' => $expense->category_id, 'date' => '2026-10-10',
        'return' => 'https://evil.example.com/',
    ])->assertRedirect(route('dashboard'));
});

it('soft deletes so the entry can come back later', function () {
    $user = User::factory()->create();
    $expense = $user->expenses()->create([
        'amount' => 9, 'category_id' => $user->categories()->first()->id, 'date' => '2026-10-10',
    ]);

    $this->actingAs($user)->deleteJson("/expenses/{$expense->id}")->assertOk();

    expect(Expense::find($expense->id))->toBeNull()
        ->and(Expense::withTrashed()->find($expense->id))->not->toBeNull();
});
