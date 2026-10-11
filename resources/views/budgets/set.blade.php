@extends('layouts.app')

@section('title', 'Budget')

@section('content')
@php
    $noun = $period->noun();
    $total = (float) old('total_budget', $amounts['total'] ?? 0);
    $parts = $categories->mapWithKeys(fn ($c) => [
        $c->id => (float) old("budgets.{$c->id}", $amounts[$c->id] ?? 0),
    ]);
@endphp

<div class="mx-auto max-w-2xl">
    <p class="eyebrow">Budget · {{ $period->label() }}</p>
    <h1 class="mt-1 text-3xl">How much do you want to spend this {{ $noun }}?</h1>
    <p class="mt-2 text-ink-soft">
        Pick a total first. Splitting it by category is optional, but it's what makes the "over" warnings useful.
        Next {{ $noun }} starts with the same numbers unless you change them.
    </p>
    <p class="mt-2 text-sm text-ink-soft">
        You budget {{ $period->isMonthly() ? 'monthly' : 'weekly' }}.
        <a href="{{ route('profile.index') }}#preferences" class="link">Switch to {{ $period->isMonthly() ? 'weekly' : 'monthly' }}</a>.
    </p>

    @if ($isSuggestion)
        <div class="note-pine mt-6">
            These amounts are your {{ $period->isMonthly() ? 'weekly' : 'monthly' }} budget converted to a {{ $noun }}.
            Round them however you like and save.
        </div>
    @endif

    <form method="POST" action="{{ route('budgets.store') }}" class="mt-8"
          x-data="{ total: {{ $total }}, parts: @js($parts) }">
        @csrf

        <div>
            <label for="total_budget" class="label">Total for the {{ $noun }} ({{ \App\Support\Money::symbol() }})</label>
            <input type="number" id="total_budget" name="total_budget" step="0.01" min="0" required
                   x-model.number="total" value="{{ $total }}"
                   class="field num max-w-xs font-serif !text-3xl">
            @error('total_budget')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <h2 class="mt-10 text-lg">Split by category</h2>
        <table class="ledger mt-2">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="hidden !text-right sm:table-cell">Last {{ $noun }}</th>
                    <th class="!text-right">Limit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            <span class="mr-1.5 inline-block h-2.5 w-2.5 rounded-full align-middle" style="background: {{ $category->color }}"></span>
                            <label for="budget-{{ $category->id }}">{{ $category->name }}</label>
                        </td>
                        <td class="amount hidden text-ink-soft sm:table-cell">
                            {{ number_format($lastSpent[$category->id] ?? 0, 2) }}
                        </td>
                        <td class="w-36">
                            <input type="number" id="budget-{{ $category->id }}" name="budgets[{{ $category->id }}]" step="0.01" min="0"
                                   x-model.number="parts[{{ $category->id }}]"
                                   value="{{ $parts[$category->id] ?: '' }}" placeholder="0.00"
                                   class="field num !border-b-rule text-right font-mono focus:!border-b-pine">
                            @error("budgets.{$category->id}")
                                <p class="field-error text-right">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-ink-soft">No categories yet. <a href="{{ url('/categories') }}" class="link">Add some</a>.</td></tr>
                @endforelse
                @php $allocExpr = 'Object.values(parts).reduce((a, b) => a + (Number(b) || 0), 0)'; @endphp
                <tr class="total">
                    <td>Allocated</td>
                    <td class="hidden sm:table-cell"></td>
                    <td class="amount">
                        <span x-text="({{ $allocExpr }}).toFixed(2)"></span>
                        <span class="font-normal text-ink-soft">/ <span x-text="(Number(total) || 0).toFixed(2)"></span></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="mt-2 text-right text-xs" x-show="{{ $allocExpr }} > (Number(total) || 0)" x-cloak>
            <span class="text-rust">That's more than your total. Fine if you mean it, but the numbers won't add up.</span>
        </p>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="btn-ghost">Cancel</a>
            <button type="submit" class="btn-pine">Save budget</button>
        </div>
    </form>

    <aside class="mt-12 border-t border-ink pt-4 text-sm text-ink-soft">
        <p class="font-serif text-base text-ink">Not sure where to start?</p>
        <p class="mt-1">
            Use the "last {{ $noun }}" column: it's what you actually spent, not what you wish you'd spent.
            A common split is half on needs, a third on wants, and the rest saved. Adjust after a couple of {{ $noun }}s.
        </p>
    </aside>
</div>
@endsection
