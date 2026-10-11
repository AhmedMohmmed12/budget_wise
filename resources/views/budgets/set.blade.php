@extends('layouts.app')

@section('title', 'Budget')

@section('content')
@php
    $categories = [
        'restaurants' => 'Restaurants',
        'housing' => 'Housing',
        'transportation' => 'Transportation',
        'shopping' => 'Shopping',
        'entertainment' => 'Entertainment',
        'others' => 'Others',
    ];
    $initial = collect($categories)->mapWithKeys(fn ($label, $key) => [
        $key => (float) old($key, $budgetData[$label]['amount'] ?? 0),
    ]);
@endphp

<div class="mx-auto max-w-2xl">
    <p class="eyebrow">Budget</p>
    <h1 class="mt-1 text-3xl">How much do you want to spend?</h1>
    <p class="mt-2 text-ink-soft">
        Pick a total first. Splitting it into categories is optional, but it's what makes the "over" warnings useful.
    </p>

    <form method="POST" action="{{ route('budgets.store') }}" class="mt-8"
          x-data="{ total: {{ (float) old('total_budget', $budgetData['Total']['amount'] ?? 0) }}, parts: @js($initial) }">
        @csrf

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="total_budget" class="label">Total budget ($)</label>
                <input type="number" id="total_budget" name="total_budget" step="0.01" min="0" required
                       x-model.number="total"
                       value="{{ old('total_budget', $budgetData['Total']['amount'] ?? 0) }}"
                       class="field num font-serif !text-3xl">
                @error('total_budget')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="period" class="label">Starts over every</label>
                <select id="period" name="period" class="field" required>
                    <option value="monthly" @selected(old('period', $period) == 'monthly')>Month</option>
                    <option value="weekly" @selected(old('period', $period) == 'weekly')>Week</option>
                </select>
                @error('period')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <h2 class="mt-10 text-lg">Split by category</h2>
        <table class="ledger mt-2">
            <thead>
                <tr><th>Category</th><th class="!text-right">Limit ($)</th></tr>
            </thead>
            <tbody>
                @foreach ($categories as $key => $label)
                    <tr>
                        <td><label for="{{ $key }}">{{ $label }}</label></td>
                        <td class="w-40">
                            <input type="number" id="{{ $key }}" name="{{ $key }}" step="0.01" min="0"
                                   x-model.number="parts.{{ $key }}"
                                   value="{{ old($key, $budgetData[$label]['amount'] ?? 0) }}"
                                   placeholder="0.00"
                                   class="field num !border-b-rule text-right font-mono focus:!border-b-pine">
                            @error($key)
                                <p class="field-error text-right">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td>Allocated</td>
                    <td class="amount">
                        @php $allocExpr = "Object.values(parts).reduce((a, b) => a + (Number(b) || 0), 0)"; @endphp
                        <span x-text="({{ $allocExpr }}).toFixed(2)"></span>
                        <span class="font-normal text-ink-soft">/ <span x-text="(Number(total) || 0).toFixed(2)"></span></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="mt-2 text-right text-xs"
           x-show="{{ $allocExpr }} > (Number(total) || 0)" x-cloak>
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
            Look at what you actually spent last month and use that, not what you wish you'd spent.
            A common split is half on needs, a third on wants, and the rest saved. Adjust after a month or two.
        </p>
    </aside>
</div>
@endsection
