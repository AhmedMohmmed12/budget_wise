<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-10-12 10:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('adds a category at the end of the list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/categories', ['name' => 'Groceries', 'color' => '#5f7a3a'])->assertRedirect();

    expect($user->categories()->ordered()->get()->last())
        ->name->toBe('Groceries')
        ->color->toBe('#5f7a3a');
});

it('rejects duplicate names and colours outside the palette', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/categories', ['name' => 'Housing', 'color' => '#ff00ff'])
        ->assertSessionHasErrors(['name', 'color']);
});

it('lets two users have the same category name', function () {
    User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->post('/categories', ['name' => 'Groceries', 'color' => '#5f7a3a'])->assertSessionHasNoErrors();
    expect(Category::where('name', 'Groceries')->count())->toBe(1);
});

it('renames and recolours', function () {
    $user = User::factory()->create();
    $category = $user->categories()->where('name', 'Others')->first();

    $this->actingAs($user)->patch("/categories/{$category->id}", ['name' => 'Bits and bobs', 'color' => '#a33f5c'])
        ->assertSessionHasNoErrors();

    expect($category->fresh())->name->toBe('Bits and bobs')->color->toBe('#a33f5c');
});

it('archives and unarchives, hiding archived ones from the add form', function () {
    $user = User::factory()->create();
    $category = $user->categories()->where('name', 'Shopping')->first();

    $this->actingAs($user)->post("/categories/{$category->id}/archive");
    expect($category->fresh()->isArchived())->toBeTrue();

    $this->actingAs($user)->get('/dashboard')->assertDontSee('<option value="'.$category->id.'">', false);

    $this->actingAs($user)->post("/categories/{$category->id}/archive");
    expect($category->fresh()->isArchived())->toBeFalse();
});

it('moves categories up and down', function () {
    $user = User::factory()->create();
    $housing = $user->categories()->where('name', 'Housing')->first();

    $this->actingAs($user)->post("/categories/{$housing->id}/move", ['direction' => 'up']);

    expect($user->categories()->ordered()->pluck('name')->take(2)->all())->toBe(['Housing', 'Restaurants']);
});

it('deletes unused categories but refuses used ones', function () {
    $user = User::factory()->create();
    $unused = $user->categories()->where('name', 'Entertainment')->first();
    $withExpense = $user->categories()->where('name', 'Housing')->first();
    $withBudget = $user->categories()->where('name', 'Shopping')->first();
    $user->expenses()->create(['amount' => 1, 'category_id' => $withExpense->id, 'date' => '2026-10-01']);
    Budget::create(['user_id' => $user->id, 'category_id' => $withBudget->id, 'period' => 'monthly', 'period_start' => '2026-10-01', 'amount' => 5]);

    $this->actingAs($user)->delete("/categories/{$unused->id}")->assertSessionHas('success');
    $this->actingAs($user)->delete("/categories/{$withExpense->id}")->assertSessionHas('error');
    $this->actingAs($user)->delete("/categories/{$withBudget->id}")->assertSessionHas('error');

    expect(Category::find($unused->id))->toBeNull()
        ->and(Category::find($withExpense->id))->not->toBeNull()
        ->and(Category::find($withBudget->id))->not->toBeNull();
});

it('keeps other users out', function () {
    $owner = User::factory()->create();
    $category = $owner->categories()->first();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->patch("/categories/{$category->id}", ['name' => 'Mine', 'color' => '#2f5d50'])->assertNotFound();
    $this->actingAs($intruder)->post("/categories/{$category->id}/archive")->assertNotFound();
    $this->actingAs($intruder)->delete("/categories/{$category->id}")->assertNotFound();
});
