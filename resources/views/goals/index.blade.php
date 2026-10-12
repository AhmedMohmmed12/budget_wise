@extends('layouts.app')

@section('title', 'Goals')

@section('content')
<div class="mx-auto max-w-3xl">
    <p class="eyebrow">Goals</p>
    <h1 class="mt-1 text-3xl">Saving up for something</h1>
    <p class="mt-2 text-ink-soft">
        Money you put toward a goal counts as "set aside", not spending, so it won't eat into your budget.
    </p>

    @forelse ($active as $goal)
        @php
            $bag = $errors->getBag("goal{$goal->id}");
            $projected = $goal->projectedDate($today);
            $needed = $goal->neededPerMonth($today);
        @endphp
        <section class="mt-8 border-t-2 border-ink pt-4" x-data="{ adding: {{ $bag->any() ? 'true' : 'false' }} }">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-xl">{{ $goal->name }}</h2>
                <span class="text-sm">
                    <a href="{{ route('goals.edit', $goal) }}" class="link text-ink-soft">edit</a>
                </span>
            </div>

            <p class="num mt-1 font-mono text-sm">
                @money($goal->savedAmount()) <span class="text-ink-soft">of @money($goal->target_amount)</span>
                @if ($goal->target_date) <span class="ml-1 text-ink-soft">· by {{ $goal->target_date->format('M Y') }}</span> @endif
            </p>
            <div class="bar mt-2"><span class="!bg-pine" style="width: {{ $goal->percentage() }}%"></span></div>

            <p class="mt-2 text-sm text-ink-soft">
                @if ($goal->isReached())
                    <span class="text-pine">You've got the full amount.</span>
                    <form method="POST" action="{{ route('goals.complete', $goal) }}" class="inline">
                        @csrf
                        <button type="submit" class="link text-ink">Mark it done</button>
                    </form>
                @else
                    @money($goal->remaining()) to go.
                    @if ($projected)
                        At your recent pace you'll get there around {{ $projected->format('F Y') }}.
                    @else
                        Nothing put aside in the last three months yet.
                    @endif
                    @if ($needed)
                        To make {{ $goal->target_date->format('M Y') }}, put aside about @money($needed) a month.
                    @endif
                @endif
            </p>

            <div class="mt-3 flex gap-3">
                <button type="button" class="btn-ink !px-4 !py-1.5" @click="adding = !adding" x-text="adding ? 'Close' : 'Put money in or out'">Put money in or out</button>
            </div>

            <form method="POST" action="{{ route('goals.contributions.store', $goal) }}" x-show="adding" x-cloak
                  class="mt-4 grid gap-4 border-y border-dotted border-rule py-4 sm:grid-cols-[auto_8rem_10rem_minmax(0,1fr)_auto] sm:items-end">
                @csrf
                <fieldset class="flex gap-4 pb-1.5 text-sm">
                    <label class="flex items-center gap-1.5"><input type="radio" name="direction" value="in" @checked(old('direction', 'in') === 'in') class="border-ink text-pine focus:ring-pine"> In</label>
                    <label class="flex items-center gap-1.5"><input type="radio" name="direction" value="out" @checked(old('direction') === 'out') class="border-ink text-pine focus:ring-pine"> Out</label>
                </fieldset>
                <div>
                    <label for="c-amount-{{ $goal->id }}" class="label">Amount</label>
                    <input type="number" id="c-amount-{{ $goal->id }}" name="amount" step="0.01" min="0.01" required class="field num font-mono"
                           value="{{ $bag->any() ? old('amount') : '' }}">
                    @foreach ($bag->get('amount') as $message) <p class="field-error">{{ $message }}</p> @endforeach
                </div>
                <div>
                    <label for="c-date-{{ $goal->id }}" class="label">Date</label>
                    <input type="date" id="c-date-{{ $goal->id }}" name="date" required class="field font-mono"
                           min="{{ $openRange[0] }}" max="{{ $openRange[1] }}" value="{{ $bag->any() ? old('date') : $openRange[1] }}">
                    @foreach ($bag->get('date') as $message) <p class="field-error">{{ $message }}</p> @endforeach
                </div>
                <div>
                    <label for="c-note-{{ $goal->id }}" class="label">Note</label>
                    <input type="text" id="c-note-{{ $goal->id }}" name="note" maxlength="255" class="field" value="{{ $bag->any() ? old('note') : '' }}">
                </div>
                <button type="submit" class="btn-pine">Save</button>
            </form>

            @if ($goal->contributions->isNotEmpty())
                <table class="ledger mt-4">
                    <tbody>
                        @foreach ($goal->contributions->take(5) as $contribution)
                            <tr>
                                <td class="w-24 whitespace-nowrap font-mono text-ink-soft">{{ $contribution->date->format('M d') }}</td>
                                <td class="text-ink-soft">{{ $contribution->note }}</td>
                                <td class="amount {{ $contribution->amount < 0 ? 'text-rust' : 'text-pine' }}">{{ $contribution->amount < 0 ? '' : '+' }}@money($contribution->amount)</td>
                                <td class="w-16 text-right text-sm">
                                    @if (\App\Support\EntryWindow::isOpen($contribution->date, Auth::user()))
                                        <form method="POST" action="{{ route('goals.contributions.destroy', $contribution) }}" onsubmit="return confirm('Remove this?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-1 text-lg leading-none text-ink-soft hover:text-rust" aria-label="Remove">&times;</button>
                                        </form>
                                    @else
                                        <span class="pill">closed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($goal->contributions->count() > 5)
                    <p class="mt-1 text-xs text-ink-soft">and {{ $goal->contributions->count() - 5 }} earlier.</p>
                @endif
            @endif
        </section>
    @empty
        <p class="mt-8 border-t border-ink pt-4 text-ink-soft">No goals yet. A new laptop, a trip, a rainy-day fund: start one below.</p>
    @endforelse

    <section class="mt-12">
        <h2 class="border-b border-ink pb-1.5 text-lg">New goal</h2>
        <form method="POST" action="{{ route('goals.store') }}" class="mt-5 grid gap-5 sm:grid-cols-[minmax(0,1fr)_9rem_10rem_auto] sm:items-end">
            @csrf
            <div>
                <label for="g-name" class="label">Saving for</label>
                <input type="text" id="g-name" name="name" maxlength="100" required class="field" placeholder="New laptop" value="{{ old('name') }}">
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="g-target" class="label">Target ({{ \App\Support\Money::symbol() }})</label>
                <input type="number" id="g-target" name="target_amount" step="0.01" min="1" required class="field num font-mono" value="{{ old('target_amount') }}">
                @error('target_amount') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="g-date" class="label">By (optional)</label>
                <input type="date" id="g-date" name="target_date" class="field font-mono" value="{{ old('target_date') }}">
                @error('target_date') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-pine">Add goal</button>
        </form>
    </section>

    @if ($done->isNotEmpty())
        <section class="mt-12">
            <h2 class="border-b border-ink pb-1.5 text-lg">Done</h2>
            <ul class="mt-2 text-sm">
                @foreach ($done as $goal)
                    <li class="flex items-center justify-between border-b border-dotted border-rule py-2">
                        <span>{{ $goal->name }} <span class="ml-1 text-ink-soft">· @money($goal->savedAmount()) · {{ $goal->completed_at->format('M Y') }}</span></span>
                        <form method="POST" action="{{ route('goals.complete', $goal) }}">
                            @csrf
                            <button type="submit" class="link text-ink-soft">reopen</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection
