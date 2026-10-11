@extends('layouts.app')

@section('title', 'Edit income')

@section('content')
<div class="mx-auto max-w-xl">
    <p class="eyebrow">Edit income</p>
    <h1 class="mt-1 text-3xl">{{ $income->source }}</h1>
    <p class="mt-1 text-sm text-ink-soft">Written down {{ $income->created_at->diffForHumans() }}.</p>

    <form method="POST" action="{{ route('incomes.update', $income) }}" class="mt-8 space-y-6">
        @csrf
        @method('PATCH')
        <input type="hidden" name="return" value="{{ $returnTo }}">

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="amount" class="label">Amount ({{ \App\Support\Money::symbol() }})</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required
                       value="{{ old('amount', $income->amount) }}" class="field num font-mono !text-xl">
                @error('amount') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="date" class="label">Date</label>
                <input type="date" id="date" name="date" required
                       min="{{ $openRange[0] }}" max="{{ $openRange[1] }}"
                       value="{{ old('date', $income->date->toDateString()) }}" class="field font-mono">
                @error('date') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="source" class="label">From</label>
            <input type="text" id="source" name="source" maxlength="100" required
                   value="{{ old('source', $income->source) }}" class="field">
            @error('source') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="note" class="label">Note</label>
            <input type="text" id="note" name="note" maxlength="500"
                   value="{{ old('note', $income->note) }}" class="field">
            @error('note') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            <button type="submit" form="delete-income" class="link text-sm text-rust">Delete entry</button>
            <div class="flex gap-3">
                <a href="{{ $returnTo }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-pine">Save</button>
            </div>
        </div>
    </form>

    <form id="delete-income" method="POST" action="{{ route('incomes.destroy', $income) }}"
          onsubmit="return confirm('Delete this entry? You can bring it back from Recently deleted.')">
        @csrf
        @method('DELETE')
        <input type="hidden" name="return" value="{{ $returnTo }}">
    </form>
</div>
@endsection
