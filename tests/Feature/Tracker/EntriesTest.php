<?php

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-10-12 10:00:00'));
afterEach(fn () => Carbon::setTestNow());

function spend(User $user, float $amount, string $date, string $category = 'Others', ?string $note = null): Expense
{
    return Expense::create([
        'user_id' => $user->id,
        'category_id' => $user->categories()->where('name', $category)->value('id'),
        'amount' => $amount,
        'date' => $date,
        'note' => $note,
    ]);
}

it('lists this month by default with a total', function () {
    $user = User::factory()->create();
    spend($user, 10, '2026-10-02', note: 'October thing');
    spend($user, 99, '2026-09-20', note: 'September thing');

    $this->actingAs($user)->get('/entries')
        ->assertOk()
        ->assertSee('October 2026')
        ->assertSee('October thing')
        ->assertDontSee('September thing')
        ->assertSee('$10.00');
});

it('filters by month, category, text and amount', function () {
    $user = User::factory()->create();
    spend($user, 4.5, '2026-09-03', 'Restaurants', 'Coffee with Omar');
    spend($user, 60, '2026-09-04', 'Restaurants', 'Dinner out');
    spend($user, 5, '2026-09-05', 'Shopping', 'Coffee beans');
    $restaurants = $user->categories()->where('name', 'Restaurants')->value('id');

    $this->actingAs($user)
        ->get("/entries?month=2026-09&category={$restaurants}&q=coffee&max=10")
        ->assertOk()
        ->assertSee('Coffee with Omar')
        ->assertDontSee('Dinner out')
        ->assertDontSee('Coffee beans')
        ->assertSee('Filtered total');
});

it('treats an empty month as all time', function () {
    $user = User::factory()->create();
    spend($user, 1, '2025-01-05', note: 'Long ago');

    $this->actingAs($user)->get('/entries?month=')->assertSee('All time')->assertSee('Long ago');
});

it('only shows the user\'s own entries', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    spend($other, 1, '2026-10-05', note: 'Not yours');

    $this->actingAs($user)->get('/entries?month=all')->assertDontSee('Not yours');
});

it('exports the filtered entries as CSV', function () {
    $user = User::factory()->create(['currency' => 'EGP']);
    spend($user, 12.5, '2026-10-03', 'Restaurants', 'Lunch');
    spend($user, 7, '2026-10-04', 'Shopping', '=HYPERLINK("bad")');
    spend($user, 3, '2026-09-30', 'Shopping', 'Last month');
    spend(User::factory()->create(), 1, '2026-10-03', note: 'Someone else');

    $response = $this->actingAs($user)->get('/entries/export?month=2026-10');
    $csv = $response->streamedContent();

    $response->assertOk()->assertDownload('budgetwise-expenses-2026-10.csv');
    expect($csv)
        ->toContain("Date,Category,Note,Amount,Currency\n")
        ->toContain('2026-10-03,Restaurants,Lunch,12.50,EGP')
        ->toContain("'=HYPERLINK")
        ->not->toContain('Last month')
        ->not->toContain('Someone else');
});

it('restores deleted entries while their month is open', function () {
    $user = User::factory()->create();
    $expense = spend($user, 8, '2026-10-05', note: 'Oops');
    $expense->delete();

    $this->actingAs($user)->get('/entries/trash')->assertSee('Oops');
    $this->actingAs($user)->post("/entries/trash/expenses/{$expense->id}/restore")->assertRedirect();

    expect(Expense::find($expense->id))->not->toBeNull();
});

it('will not restore into a closed month, but can delete for good', function () {
    $user = User::factory()->create();
    $expense = spend($user, 8, '2026-09-05');
    $expense->delete();

    $this->actingAs($user)->post("/entries/trash/expenses/{$expense->id}/restore")->assertForbidden();
    $this->actingAs($user)->delete("/entries/trash/expenses/{$expense->id}")->assertRedirect();

    expect(Expense::withTrashed()->find($expense->id))->toBeNull();
});

it('keeps other users out of the trash', function () {
    $owner = User::factory()->create();
    $expense = spend($owner, 8, '2026-10-05');
    $expense->delete();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->post("/entries/trash/expenses/{$expense->id}/restore")->assertNotFound();
    $this->actingAs($intruder)->delete("/entries/trash/expenses/{$expense->id}")->assertNotFound();
});

it('prunes entries deleted more than 30 days ago', function () {
    $user = User::factory()->create();
    $old = spend($user, 1, '2026-10-01');
    $recent = spend($user, 2, '2026-10-02');
    $old->delete();
    $recent->delete();
    Expense::withTrashed()->whereKey($old->id)->update(['deleted_at' => now()->subDays(31)]);

    $this->artisan('model:prune', ['--model' => [Expense::class]])->assertSuccessful();

    expect(Expense::withTrashed()->find($old->id))->toBeNull()
        ->and(Expense::withTrashed()->find($recent->id))->not->toBeNull();
});
