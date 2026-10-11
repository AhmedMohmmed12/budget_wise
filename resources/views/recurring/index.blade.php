@extends('layouts.app')

@section('title', 'Recurring')

@section('content')
<div class="mx-auto max-w-3xl">
    <p class="eyebrow">Recurring</p>
    <h1 class="mt-1 text-3xl">Things that happen every month</h1>
    <p class="mt-2 text-ink-soft">
        Rent, subscriptions, your salary. They're written down for you on the day, so you don't have to remember.
    </p>

    @if ($entries->isEmpty())
        <p class="mt-8 border-t border-ink pt-4 text-ink-soft">Nothing yet. Add your first one below.</p>
    @else
        <table class="ledger mt-8">
            <thead>
                <tr><th>What</th><th>Next</th><th class="!text-right">Amount</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    @php $next = $entry->isPaused() ? null : $entry->nextDate($today); @endphp
                    <tr class="{{ $entry->isPaused() ? 'opacity-60' : '' }}">
                        <td>
                            @if ($entry->isExpense())
                                <span class="mr-1.5 inline-block h-2 w-2 rounded-full align-middle" style="background: {{ $entry->category?->color }}"></span>
                            @endif
                            {{ $entry->description }}
                            <span class="text-ink-soft">· {{ $entry->isExpense() ? $entry->category?->name : 'income' }}</span>
                            @if ($entry->isPaused()) <span class="pill ml-1">paused</span> @endif
                            <span class="block font-mono text-xs text-ink-soft">every month on day {{ $entry->day_of_month }}</span>
                        </td>
                        <td class="whitespace-nowrap font-mono text-sm text-ink-soft">
                            {{ $next ? $next->format('M j') : ($entry->isPaused() ? '—' : 'ended') }}
                        </td>
                        <td class="amount {{ $entry->isExpense() ? '' : 'text-pine' }}">{{ $entry->isExpense() ? '' : '+' }}@money($entry->amount)</td>
                        <td class="whitespace-nowrap text-right text-sm">
                            <a href="{{ route('recurring.edit', $entry) }}" class="link text-ink-soft">edit</a>
                            <form method="POST" action="{{ route('recurring.pause', $entry) }}" class="ml-2 inline">
                                @csrf
                                <button type="submit" class="link text-ink-soft">{{ $entry->isPaused() ? 'resume' : 'pause' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <section class="mt-12">
        <h2 class="border-b border-ink pb-1.5 text-lg">Add one</h2>
        <form method="POST" action="{{ route('recurring.store') }}" class="mt-5">
            @csrf
            @include('recurring._form')
            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-pine">Save</button>
            </div>
        </form>
    </section>
</div>
@endsection
