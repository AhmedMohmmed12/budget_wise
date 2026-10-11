@extends('layouts.app')

@section('title', 'This month')

@section('content')
@php
    $s = $budgetSummary;
    $now = now();
    $weekly = ($s['period'] ?? 'monthly') === 'weekly';
    $periodName = $weekly ? 'this week' : $now->format('F');
    $dayOf = $weekly ? 'day ' . $now->dayOfWeekIso . ' of 7' : 'day ' . $now->day . ' of ' . $now->daysInMonth;
    $daysLeft = max(1, (int) floor($s['daysRemaining']));
    $hasBudget = $s['totalBudget'] > 0;
    $over = $hasBudget && $s['remaining'] < 0;
    $firstName = \Illuminate\Support\Str::of(Auth::user()->name)->before(' ');

    $rows = collect($s['categorySummaries'])->filter(fn ($c) => $c['spent'] > 0 || $c['budget'] > 0);
    $mostExceeded = $rows->filter(fn ($c) => $c['budget'] > 0 && $c['spent'] > $c['budget'])
        ->sortByDesc('percentage')->first();
@endphp

<div class="grid gap-8 md:grid-cols-2">
    {{-- Left: what's left --}}
    <section>
        <p class="eyebrow">{{ $now->format('F Y') }} · {{ $dayOf }}</p>

        @if (! $hasBudget)
            <div class="money-card mt-3">
                <p class="text-sm opacity-85">Hey {{ $firstName }},</p>
                <p class="font-serif text-3xl leading-tight">no budget yet.</p>
                <p class="mt-1 text-sm opacity-85">
                    Set how much you want to spend {{ $weekly ? 'each week' : 'each month' }} and this page will keep count for you.
                </p>
                <a href="{{ route('budgets.index') }}" class="btn mt-4 bg-paper text-pine hover:bg-white">Set a budget</a>
            </div>
        @elseif ($over)
            <div class="money-card is-over mt-3">
                <p class="text-sm opacity-85">Hey {{ $firstName }}, you're</p>
                <p class="num font-serif text-[40px] leading-tight tracking-tight">${{ number_format(abs($s['remaining']), 2) }} over</p>
                <p class="text-sm opacity-85">for {{ $periodName }}. It happens. Try to keep the next {{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} quiet.</p>
            </div>
        @else
            <div class="money-card mt-3">
                <p class="text-sm opacity-85">Hey {{ $firstName }}, you've got</p>
                <p class="num font-serif text-[40px] leading-tight tracking-tight">${{ number_format($s['remaining'], 2) }} left</p>
                <p class="text-sm opacity-85">
                    That's about ${{ number_format($s['dailyBudget'], 0) }} a day for the next {{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }}.
                    @if ($s['spentPercentage'] < 75) You're doing fine. @elseif ($s['spentPercentage'] < 90) Getting close, go easy. @else Nearly at the limit. @endif
                </p>
            </div>
        @endif

        @if ($hasBudget)
            <div class="bar mt-4"><span style="width: {{ min($s['spentPercentage'], 100) }}%" class="{{ $over ? '!bg-rust' : '' }}"></span></div>
            <div class="num mt-1.5 flex justify-between font-mono text-[11px] text-ink-soft">
                <span>spent {{ number_format($s['totalSpent'], 2) }}</span>
                <span>of {{ number_format($s['totalBudget'], 2) }}</span>
            </div>
        @endif

        <p class="mt-4 text-sm text-ink-soft">
            Spent today: <span class="num font-mono text-ink">${{ number_format($s['todaySpending'], 2) }}</span>
        </p>

        @if ($mostExceeded && ! $over)
            <div class="note mt-4">
                {{ $mostExceeded['category'] }} went
                <b>${{ number_format($mostExceeded['spent'] - $mostExceeded['budget'], 2) }} over</b>
                {{ $periodName === 'this week' ? 'this week' : 'this month' }}.
                <a href="{{ route('expense-tips') }}" class="underline underline-offset-2">A few ideas to pull it back</a>.
            </div>
        @endif
    </section>

    {{-- Right: category ledger --}}
    <section>
        <div class="flex items-baseline justify-between">
            <h2 class="text-lg">Where it went</h2>
            <a href="{{ route('budgets.index') }}" class="link text-sm text-ink-soft">Change limits</a>
        </div>

        @if ($rows->isEmpty())
            <p class="mt-3 border-t border-ink pt-3 text-sm text-ink-soft">
                Nothing written down yet. Add your first expense and it'll show up here.
            </p>
        @else
            <table class="ledger mt-2">
                <thead>
                    <tr><th>Category</th><th class="!text-right">Spent / limit</th></tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php $rowOver = $row['budget'] > 0 && $row['spent'] > $row['budget']; @endphp
                        <tr class="{{ $rowOver ? 'text-rust' : '' }}">
                            <td>
                                {{ $row['category'] }}
                                @if ($rowOver)
                                    <span class="pill-over ml-1">over</span>
                                @elseif ($row['budget'] > 0 && $row['spent'] >= $row['budget'])
                                    <span class="pill ml-1">at limit</span>
                                @endif
                            </td>
                            <td class="amount">
                                {{ number_format($row['spent'], 2) }}
                                <span class="text-ink-soft">/ {{ $row['budget'] > 0 ? number_format($row['budget'], 0) : '—' }}</span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td>Total</td>
                        <td class="amount">{{ number_format($s['totalSpent'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </section>
</div>

{{-- Recent entries --}}
<section class="mt-10" x-data="{ adding: {{ $recentExpenses->isEmpty() ? 'true' : 'false' }} }">
    <div class="flex items-center justify-between border-b border-ink pb-2">
        <h2 class="text-lg">Recent entries</h2>
        <button type="button" class="btn-ink !py-1.5 !px-4" @click="adding = !adding" :aria-expanded="adding">
            <span x-text="adding ? 'Close' : '+ Add expense'">+ Add expense</span>
        </button>
    </div>

    <div x-cloak x-show="adding" class="border-b border-dotted border-rule py-5">
        @include('components.expense-form')
    </div>

    @if ($recentExpenses->isEmpty())
        <p class="py-4 text-sm text-ink-soft">No entries yet. The first one's always the hardest.</p>
    @else
        <table class="ledger">
            <tbody>
                @foreach ($recentExpenses as $expense)
                    <tr>
                        <td class="w-24 whitespace-nowrap font-mono text-ink-soft">{{ $expense->date->format($expense->date->isCurrentYear() ? 'M d' : "M d 'y") }}</td>
                        <td>
                            {{ $expense->note ?: $expense->category }}
                            @if ($expense->note)
                                <span class="text-ink-soft">· {{ $expense->category }}</span>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($expense->amount, 2) }}</td>
                        <td class="w-8 text-right">
                            <button type="button" class="delete-expense px-1 text-lg leading-none text-ink-soft hover:text-rust"
                                    data-id="{{ $expense->id }}" aria-label="Delete this entry" title="Delete">&times;</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('.delete-expense');
    if (!button || !confirm('Delete this entry?')) return;

    button.disabled = true;
    try {
        const response = await fetch(`/expenses/${button.dataset.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        if (!response.ok) throw new Error();
        location.reload();
    } catch {
        button.disabled = false;
        alert("Couldn't delete that entry. Try again.");
    }
});
</script>
@endpush
