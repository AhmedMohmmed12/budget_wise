@extends('layouts.app')

@section('title', 'Entries')

@section('content')
@php
    $filtering = $filters['category'] || $filters['q'] || $filters['min'] !== null || $filters['max'] !== null;
    $monthLabel = $filters['month'] === 'all' ? 'All time' : \Carbon\Carbon::parse($filters['month'].'-01')->format('F Y');
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

{{-- Filters --}}
<form method="GET" action="{{ route('entries.index') }}" class="mt-6 grid gap-4 border-y border-ink py-4 sm:grid-cols-[10rem_minmax(0,1fr)_minmax(0,1fr)_6rem_6rem_auto] sm:items-end">
    <div>
        <label for="f-month" class="label">Month</label>
        <div class="flex items-center gap-2">
            <input type="month" id="f-month" name="month" max="{{ $thisMonth }}"
                   value="{{ $filters['month'] === 'all' ? '' : $filters['month'] }}" class="field font-mono">
        </div>
    </div>
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
    <div>
        <label for="f-q" class="label">Note contains</label>
        <input type="search" id="f-q" name="q" value="{{ $filters['q'] }}" placeholder="coffee" class="field">
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
            <a href="{{ route('entries.index') }}" class="btn-ghost !px-4 !py-1.5">Reset</a>
        @endif
    </div>
</form>
<p class="mt-2 text-xs text-ink-soft">
    Leave the month empty and press Filter to search all time.
    @if ($filters['month'] !== 'all')
        <a href="{{ route('entries.index', array_merge(request()->query(), ['month' => 'all'])) }}" class="link">Show all time</a>
    @endif
</p>

@if ($expenses->isEmpty())
    <p class="mt-8 text-ink-soft">
        {{ $filtering ? 'Nothing matches those filters.' : 'No entries for '.$monthLabel.'.' }}
    </p>
@else
    <div class="mt-6">
        @include('expenses._rows', ['expenses' => $expenses])
    </div>
    <div class="flex justify-between border-t-[3px] border-double border-ink pt-2 text-sm font-bold">
        <span>{{ $filtering ? 'Filtered total' : 'Total' }} · {{ $expenses->total() }} {{ \Illuminate\Support\Str::plural('entry', $expenses->total()) }}</span>
        <span class="num font-mono">@money($total)</span>
    </div>
    {{ $expenses->links('partials.pagination') }}
@endif
@endsection
