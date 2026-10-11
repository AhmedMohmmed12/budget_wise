@extends('layouts.app')

@section('title', 'Entries')

@section('content')
@php
    $isIncome = $filters['type'] === 'income';
    $filtering = (! $isIncome && $filters['category']) || $filters['q'] || $filters['min'] !== null || $filters['max'] !== null;
    $monthLabel = $filters['month'] === 'all' ? 'All time' : \Carbon\Carbon::parse($filters['month'].'-01')->format('F Y');
    $tab = fn ($type) => route('entries.index', array_filter(['type' => $type === 'income' ? 'income' : null, 'month' => $filters['month']]));
@endphp

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow">Entries</p>
        <h1 class="mt-1 text-3xl">{{ $monthLabel }}</h1>
    </div>
    <div class="flex items-center gap-4 text-sm">
        <a href="{{ route('trash.index') }}" class="link text-ink-soft">Recently deleted</a>
        <a href="{{ route('entries.export', request()->query()) }}" class="btn-ghost !px-4 !py-1.5">Export CSV</a>
    </div>
</div>

<nav class="mt-6 flex gap-6 border-b border-ink text-sm" aria-label="Entry type">
    <a href="{{ $tab('expenses') }}" class="-mb-px pb-2 {{ $isIncome ? 'text-ink-soft hover:text-ink' : 'border-b-2 border-pine font-bold' }}">Expenses</a>
    <a href="{{ $tab('income') }}" class="-mb-px pb-2 {{ $isIncome ? 'border-b-2 border-pine font-bold' : 'text-ink-soft hover:text-ink' }}">Income</a>
</nav>

{{-- Filters --}}
<form method="GET" action="{{ route('entries.index') }}" class="grid gap-4 border-b border-ink py-4 sm:grid-cols-[10rem_minmax(0,1fr)_minmax(0,1fr)_6rem_6rem_auto] sm:items-end">
    @if ($isIncome) <input type="hidden" name="type" value="income"> @endif
    <div>
        <label for="f-month" class="label">Month</label>
        <input type="month" id="f-month" name="month" max="{{ $thisMonth }}"
               value="{{ $filters['month'] === 'all' ? '' : $filters['month'] }}" class="field font-mono">
    </div>
    @if ($isIncome)
        <div class="hidden sm:block"></div>
    @else
        <div>
            <label for="f-category" class="label">Category</label>
            <select id="f-category" name="category" class="field">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>
                        {{ $category->name }}{{ $category->isArchived() ? ' (archived)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
    <div>
        <label for="f-q" class="label">{{ $isIncome ? 'Source or note contains' : 'Note contains' }}</label>
        <input type="search" id="f-q" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isIncome ? 'salary' : 'coffee' }}" class="field">
    </div>
    <div>
        <label for="f-min" class="label">Min</label>
        <input type="number" id="f-min" name="min" step="0.01" min="0" value="{{ $filters['min'] }}" class="field num font-mono">
    </div>
    <div>
        <label for="f-max" class="label">Max</label>
        <input type="number" id="f-max" name="max" step="0.01" min="0" value="{{ $filters['max'] }}" class="field num font-mono">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="btn-ink !px-4 !py-1.5">Filter</button>
        @if ($filtering || $filters['month'] !== $thisMonth)
            <a href="{{ $isIncome ? route('entries.index', ['type' => 'income']) : route('entries.index') }}" class="btn-ghost !px-4 !py-1.5">Reset</a>
        @endif
    </div>
</form>
<p class="mt-2 text-xs text-ink-soft">
    Leave the month empty and press Filter to search all time.
    @if ($filters['month'] !== 'all')
        <a href="{{ route('entries.index', array_merge(request()->query(), ['month' => 'all'])) }}" class="link">Show all time</a>
    @endif
</p>

@if ($entries->isEmpty())
    <p class="mt-8 text-ink-soft">
        {{ $filtering ? 'Nothing matches those filters.' : 'No '.($isIncome ? 'income' : 'expenses').' for '.$monthLabel.'.' }}
    </p>
@else
    <div class="mt-6">
        @include($isIncome ? 'incomes._rows' : 'expenses._rows', [$isIncome ? 'incomes' : 'expenses' => $entries])
    </div>
    <div class="flex justify-between border-t-[3px] border-double border-ink pt-2 text-sm font-bold">
        <span>{{ $filtering ? 'Filtered total' : 'Total' }} · {{ $entries->total() }} {{ \Illuminate\Support\Str::plural('entry', $entries->total()) }}</span>
        <span class="num font-mono {{ $isIncome ? 'text-pine' : '' }}">@money($total)</span>
    </div>
    {{ $entries->links('partials.pagination') }}
@endif
@endsection
