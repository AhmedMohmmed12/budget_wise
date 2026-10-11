@extends('layouts.app')

@section('title', $period->isMonthly() ? 'This month' : 'This week')

@section('content')
@php
    $s = $summary;
    $firstName = \Illuminate\Support\Str::of(Auth::user()->name)->before(' ');
    $noun = $period->noun();
    $worst = $s->worstCategory();
    $prev = $period->previous();
    $next = $period->next();
@endphp

{{-- Period navigation --}}
<div class="flex items-center justify-between gap-4">
    <p class="eyebrow">
        {{ $period->label() }}
        @if ($s->isCurrent)
            · {{ $s->daysLeft }} {{ \Illuminate\Support\Str::plural('day', $s->daysLeft) }} left
        @endif
    </p>
    <div class="flex items-center gap-3 font-mono text-xs">
        <a href="{{ route('dashboard', ['period' => $prev->key()]) }}" class="link text-ink-soft">&larr; previous</a>
        @unless ($s->isCurrent)
            <a href="{{ route('dashboard', ['period' => $next->key()]) }}" class="link text-ink-soft">next &rarr;</a>
            <a href="{{ route('dashboard') }}" class="link text-ink">today</a>
        @endunless
    </div>
</div>

<div class="mt-3 grid gap-8 md:grid-cols-2">
    {{-- Left: what's left --}}
    <section>
        @if (! $s->hasBudget())
            <div class="money-card">
                <p class="text-sm opacity-85">{{ $s->isCurrent ? "Hey $firstName," : 'For this '.$noun.',' }}</p>
                <p class="font-serif text-3xl leading-tight">no budget {{ $s->isCurrent ? 'yet' : 'was set' }}.</p>
                @if ($s->isCurrent)
                    <p class="mt-1 text-sm opacity-85">
                        Set how much you want to spend each {{ $noun }} and this page will keep count for you.
                    </p>
                    <a href="{{ route('budgets.index') }}" class="btn mt-4 bg-paper text-pine hover:bg-white">Set a budget</a>
                @else
                    <p class="mt-1 text-sm opacity-85">You spent @money($s->totalSpent) in total.</p>
                @endif
            </div>
        @elseif ($s->isOver())
            <div class="money-card is-over">
                <p class="text-sm opacity-85">{{ $s->isCurrent ? "Hey $firstName, you're" : 'This '.$noun.' went' }}</p>
                <p class="num font-serif text-[40px] leading-tight tracking-tight">@money(abs($s->remaining)) over</p>
                <p class="text-sm opacity-85">
                    @if ($s->isCurrent)
                        It happens. Try to keep the next {{ $s->daysLeft }} {{ \Illuminate\Support\Str::plural('day', $s->daysLeft) }} quiet.
                    @else
                        Spent @money($s->totalSpent) against a budget of @money($s->totalBudget).
                    @endif
                </p>
            </div>
        @else
            <div class="money-card">
                <p class="text-sm opacity-85">{{ $s->isCurrent ? "Hey $firstName, you've got" : 'This '.$noun.' you had' }}</p>
                <p class="num font-serif text-[40px] leading-tight tracking-tight">@money($s->remaining) left</p>
                <p class="text-sm opacity-85">
                    @if ($s->isCurrent)
                        That's about @money($s->dailyAllowance) a day for the next {{ $s->daysLeft }} {{ \Illuminate\Support\Str::plural('day', $s->daysLeft) }}.
                        @if ($s->spentPercentage < 75) You're doing fine. @elseif ($s->spentPercentage < 90) Getting close, go easy. @else Nearly at the limit. @endif
                    @else
                        Nicely done. Spent @money($s->totalSpent) of @money($s->totalBudget).
                    @endif
                </p>
            </div>
        @endif

        @if ($s->hasBudget())
            <div class="bar mt-4"><span style="width: {{ min($s->spentPercentage, 100) }}%" class="{{ $s->isOver() ? '!bg-rust' : '' }}"></span></div>
            <div class="num mt-1.5 flex justify-between font-mono text-[11px] text-ink-soft">
                <span>spent @money($s->totalSpent)</span>
                <span>of @money($s->totalBudget)</span>
            </div>
        @endif

        <p class="mt-4 text-sm text-ink-soft">
            @if ($s->isCurrent)
                Spent today: <span class="num font-mono text-ink">@money($s->todaySpent)</span>
            @endif
            @unless ($period->isMonthly())
                <span class="{{ $s->isCurrent ? 'ml-3' : '' }}">This month so far: <span class="num font-mono text-ink">@money($s->monthSpent)</span></span>
            @endunless
        </p>

        @if ($worst && ! $s->isOver())
            <div class="note mt-4">
                {{ $worst['category']->name }} went
                <b>@money($worst['spent'] - $worst['budget']) over</b>
                this {{ $noun }}.
                @if ($s->isCurrent)
                    <a href="{{ route('expense-tips') }}" class="underline underline-offset-2">A few ideas to pull it back</a>.
                @endif
            </div>
        @endif

        @if ($s->monthIncome > 0)
            <div class="mt-6">
                <p class="eyebrow">{{ $s->month->start->format('F') }} money</p>
                <dl class="mt-2 grid grid-cols-3 border-y border-ink text-sm">
                    <div class="py-2 pr-2">
                        <dt class="text-xs text-ink-soft">In</dt>
                        <dd class="num font-mono text-pine">+@money($s->monthIncome)</dd>
                    </div>
                    <div class="border-x border-dotted border-rule px-3 py-2">
                        <dt class="text-xs text-ink-soft">Out</dt>
                        <dd class="num font-mono">@money($s->monthSpent)</dd>
                    </div>
                    <div class="py-2 pl-3">
                        <dt class="text-xs text-ink-soft">Kept</dt>
                        <dd class="num font-mono font-bold {{ $s->monthLeft() < 0 ? 'text-rust' : '' }}">@money($s->monthLeft())</dd>
                    </div>
                </dl>
            </div>
        @endif

        @if ($s->upcoming()->isNotEmpty())
            <div class="mt-6">
                <div class="flex items-baseline justify-between">
                    <p class="eyebrow">Coming up this month</p>
                    <a href="{{ route('recurring.index') }}" class="link text-xs text-ink-soft">manage</a>
                </div>
                <ul class="mt-1 text-sm">
                    @foreach ($s->upcoming() as $item)
                        <li class="flex justify-between border-b border-dotted border-rule py-1.5">
                            <span><span class="mr-3 font-mono text-ink-soft">{{ $item['date']->format('M d') }}</span>{{ $item['entry']->description }}</span>
                            <span class="num font-mono {{ $item['entry']->isExpense() ? '' : 'text-pine' }}">{{ $item['entry']->isExpense() ? '' : '+' }}@money($item['entry']->amount)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    {{-- Right: category ledger --}}
    <section>
        <div class="flex items-baseline justify-between">
            <h2 class="text-lg">Where it went</h2>
            @if ($s->isCurrent)
                <a href="{{ route('budgets.index') }}" class="link text-sm text-ink-soft">Change limits</a>
            @endif
        </div>

        @if ($s->rows->isEmpty())
            <p class="mt-3 border-t border-ink pt-3 text-sm text-ink-soft">
                Nothing written down {{ $s->isCurrent ? 'yet' : 'for this '.$noun }}.
                @if ($s->isCurrent) Add your first expense and it'll show up here. @endif
            </p>
        @else
            <table class="ledger mt-2">
                <thead>
                    <tr><th>Category</th><th class="!text-right">Spent / limit</th></tr>
                </thead>
                <tbody>
                    @foreach ($s->rows as $row)
                        <tr class="{{ $row['over'] ? 'text-rust' : '' }}">
                            <td>
                                <span class="mr-1.5 inline-block h-2.5 w-2.5 rounded-full align-middle" style="background: {{ $row['category']->color }}"></span>
                                {{ $row['category']->name }}
                                @if ($row['over'])
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
                        <td class="amount">@money($s->totalSpent)</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </section>
</div>

{{-- Entries for this period --}}
<section class="mt-10" x-data="{ adding: {{ $s->isCurrent && $entries->isEmpty() ? 'true' : 'false' }} }">
    <div class="flex items-center justify-between border-b border-ink pb-2">
        <h2 class="text-lg">{{ $s->isCurrent ? 'Recent entries' : 'Entries' }}</h2>
        @if ($s->isCurrent)
            <button type="button" class="btn-ink !px-4 !py-1.5" @click="adding = !adding" :aria-expanded="adding">
                <span x-text="adding ? 'Close' : '+ Add'">+ Add</span>
            </button>
        @endif
    </div>

    @if ($s->isCurrent)
        <div x-cloak x-show="adding" class="border-b border-dotted border-rule py-5">
            @include('components.quick-add')
        </div>
    @endif

    @if ($entries->isEmpty())
        <p class="py-4 text-sm text-ink-soft">
            {{ $s->isCurrent ? "No entries yet. The first one's always the hardest." : 'No entries in this '.$noun.'.' }}
        </p>
    @else
        @include('expenses._rows', ['expenses' => $entries])
    @endif
</section>
@endsection
