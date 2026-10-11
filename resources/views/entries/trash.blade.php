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

    @if ($expenses->isEmpty())
        <p class="mt-8 border-t border-ink pt-4 text-ink-soft">Nothing here. Good.</p>
    @else
        <table class="ledger mt-6">
            <thead>
                <tr><th>Date</th><th>Entry</th><th class="!text-right">Amount</th><th class="!text-right">Deleted</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                    <tr>
                        <td class="whitespace-nowrap font-mono text-ink-soft">{{ $expense->date->format('M d, Y') }}</td>
                        <td>
                            {{ $expense->note ?: $expense->category?->name }}
                            @if ($expense->note) <span class="text-ink-soft">· {{ $expense->category?->name }}</span> @endif
                        </td>
                        <td class="amount">@money($expense->amount)</td>
                        <td class="whitespace-nowrap text-right text-xs text-ink-soft">{{ $expense->deleted_at->diffForHumans() }}</td>
                        <td class="whitespace-nowrap text-right text-sm">
                            @if (\App\Support\EntryWindow::isOpen($expense->date, Auth::user()))
                                <form method="POST" action="{{ route('trash.expenses.restore', $expense) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="link text-pine">restore</button>
                                </form>
                            @else
                                <span class="pill">closed</span>
                            @endif
                            <form method="POST" action="{{ route('trash.expenses.destroy', $expense) }}" class="ml-2 inline"
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
