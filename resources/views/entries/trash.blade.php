@extends('layouts.app')

@section('title', 'Recently deleted')

@section('content')
<div class="mx-auto max-w-3xl">
    <p class="eyebrow"><a href="{{ route('entries.index') }}" class="link">Entries</a> · Recently deleted</p>
    <h1 class="mt-1 text-3xl">Recently deleted</h1>
    <p class="mt-2 text-ink-soft">
        Deleted entries stay here for {{ config('budgetwise.trash_days') }} days, then they're gone for good.
        Entries from a closed month can't be brought back.
    </p>

    @if ($entries->isEmpty())
        <p class="mt-8 border-t border-ink pt-4 text-ink-soft">Nothing here. Good.</p>
    @else
        <table class="ledger mt-6">
            <thead>
                <tr><th>Date</th><th>Entry</th><th class="!text-right">Amount</th><th class="!text-right">Deleted</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    @php
                        $isIncome = $entry instanceof \App\Models\Income;
                        $routes = $isIncome ? ['trash.incomes.restore', 'trash.incomes.destroy'] : ['trash.expenses.restore', 'trash.expenses.destroy'];
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap font-mono text-ink-soft">{{ $entry->date->format('M d, Y') }}</td>
                        <td>
                            @if ($isIncome)
                                {{ $entry->source }} <span class="pill ml-1">income</span>
                            @else
                                {{ $entry->note ?: $entry->category?->name }}
                                @if ($entry->note) <span class="text-ink-soft">· {{ $entry->category?->name }}</span> @endif
                            @endif
                        </td>
                        <td class="amount {{ $isIncome ? 'text-pine' : '' }}">{{ $isIncome ? '+' : '' }}@money($entry->amount)</td>
                        <td class="whitespace-nowrap text-right text-xs text-ink-soft">{{ $entry->deleted_at->diffForHumans() }}</td>
                        <td class="whitespace-nowrap text-right text-sm">
                            @if (\App\Support\EntryWindow::isOpen($entry->date, Auth::user()))
                                <form method="POST" action="{{ route($routes[0], $entry) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="link text-pine">restore</button>
                                </form>
                            @else
                                <span class="pill">closed</span>
                            @endif
                            <form method="POST" action="{{ route($routes[1], $entry) }}" class="ml-2 inline"
                                  onsubmit="return confirm('Delete this entry for good?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link text-rust">delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
