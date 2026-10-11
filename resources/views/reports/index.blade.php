@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow">Reports</p>
        <h1 class="mt-1 text-3xl">{{ $year }}, month by month</h1>
    </div>
    @if (count($years) > 1)
        <nav class="flex gap-3 font-mono text-sm" aria-label="Year">
            @foreach ($years as $y)
                <a href="{{ route('reports.index', ['year' => $y]) }}" class="{{ $y === $year ? 'font-bold text-ink underline decoration-pine decoration-2 underline-offset-4' : 'link text-ink-soft' }}">{{ $y }}</a>
            @endforeach
        </nav>
    @endif
</div>

{{-- Months --}}
<div class="mt-6 overflow-x-auto">
    <table class="ledger min-w-[40rem]">
        <thead>
            <tr>
                <th>Month</th>
                <th class="w-1/3"></th>
                <th class="!text-right">In</th>
                <th class="!text-right">Spent</th>
                <th class="!text-right">Budget</th>
                <th class="!text-right">Set aside</th>
                <th class="!text-right">Kept</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($months as $m)
                @php $over = $m['budget'] > 0 && $m['spent'] > $m['budget']; @endphp
                <tr>
                    <td>
                        <a href="{{ route('entries.index', ['month' => $m['key']]) }}" class="link">{{ $m['label'] }}</a>
                        @if ($over) <span class="pill-over ml-1">over</span> @endif
                    </td>
                    <td>
                        <div class="relative h-2.5 bg-rule-light" title="Spent {{ number_format($m['spent'], 2) }} of {{ number_format($m['budget'], 2) }}">
                            <span class="absolute inset-y-0 left-0 {{ $over ? 'bg-rust' : 'bg-ink' }}" style="width: {{ $m['spent'] / $barMax * 100 }}%"></span>
                            @if ($m['budget'] > 0)
                                <span class="absolute -inset-y-1 w-0.5 bg-pine" style="left: {{ min(99.5, $m['budget'] / $barMax * 100) }}%"></span>
                            @endif
                        </div>
                    </td>
                    <td class="amount text-pine">{{ $m['income'] > 0 ? number_format($m['income'], 2) : '—' }}</td>
                    <td class="amount {{ $over ? 'text-rust' : '' }}">{{ number_format($m['spent'], 2) }}</td>
                    <td class="amount text-ink-soft">{{ $m['budget'] > 0 ? number_format($m['budget'], 2) : '—' }}</td>
                    <td class="amount text-ink-soft">{{ $m['setAside'] != 0 ? number_format($m['setAside'], 2) : '—' }}</td>
                    <td class="amount {{ $m['income'] > 0 && $m['kept'] < 0 ? 'text-rust' : '' }}">{{ $m['income'] > 0 ? number_format($m['kept'], 2) : '—' }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>{{ $year }}</td>
                <td></td>
                <td class="amount text-pine">{{ number_format($totals['income'], 2) }}</td>
                <td class="amount">{{ number_format($totals['spent'], 2) }}</td>
                <td class="amount">{{ number_format($totals['budget'], 2) }}</td>
                <td class="amount">{{ number_format($totals['setAside'], 2) }}</td>
                <td class="amount">{{ $totals['income'] > 0 ? number_format($totals['kept'], 2) : '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>
<p class="mt-2 text-xs text-ink-soft">
    Amounts in {{ Auth::user()->currency }}. The green tick on each bar marks the budget. "Kept" is what's left of income after spending and setting money aside.
</p>

<div class="mt-12 grid gap-10 md:grid-cols-2">
    {{-- Categories --}}
    <section>
        <h2 class="border-b border-ink pb-1.5 text-lg">By category</h2>
        @if ($categories->isEmpty())
            <p class="mt-3 text-sm text-ink-soft">Not enough spending yet to compare.</p>
        @else
            <table class="ledger mt-1">
                <thead>
                    <tr>
                        <th></th>
                        <th class="!text-right">{{ $today->format('M') }}</th>
                        <th class="!text-right">{{ $today->subMonthNoOverflow()->format('M') }}</th>
                        <th class="!text-right" title="Average of the three months before this one">3-mo avg</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $row)
                        @php $higher = $row['average'] > 0 && $row['this'] > $row['average'] * 1.2; @endphp
                        <tr>
                            <td>
                                <span class="mr-1.5 inline-block h-2.5 w-2.5 rounded-full align-middle" style="background: {{ $row['category']->color }}"></span>
                                {{ $row['category']->name }}
                            </td>
                            <td class="amount {{ $higher ? 'text-rust' : '' }}">{{ number_format($row['this'], 2) }}</td>
                            <td class="amount text-ink-soft">{{ number_format($row['last'], 2) }}</td>
                            <td class="amount text-ink-soft">{{ number_format($row['average'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-2 text-xs text-ink-soft">Red means this month is already 20% above your usual.</p>
        @endif
    </section>

    {{-- Weeks --}}
    @if ($weeks->isNotEmpty())
        <section>
            <h2 class="border-b border-ink pb-1.5 text-lg">Last 12 weeks</h2>
            <table class="ledger mt-1">
                <thead>
                    <tr><th>Week of</th><th class="!text-right">Spent</th><th class="!text-right">Budget</th></tr>
                </thead>
                <tbody>
                    @foreach ($weeks as $week)
                        @php $over = $week['budget'] > 0 && $week['spent'] > $week['budget']; @endphp
                        <tr>
                            <td><a href="{{ route('dashboard', ['period' => $week['period']->key()]) }}" class="link">{{ $week['period']->start->format('M j') }}</a></td>
                            <td class="amount {{ $over ? 'text-rust' : '' }}">{{ number_format($week['spent'], 2) }}</td>
                            <td class="amount text-ink-soft">{{ $week['budget'] > 0 ? number_format($week['budget'], 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif
</div>
@endsection
