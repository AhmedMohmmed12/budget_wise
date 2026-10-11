@extends('layouts.app')

@section('title', 'Edit entry')

@section('content')
<div class="mx-auto max-w-xl">
    <p class="eyebrow">Edit entry</p>
    <h1 class="mt-1 text-3xl">{{ $expense->note ?: $expense->category->name }}</h1>
    <p class="mt-1 text-sm text-ink-soft">Written down {{ $expense->created_at->diffForHumans() }}.</p>

    <form method="POST" action="{{ route('expenses.update', $expense) }}" class="mt-8 space-y-6">
        @csrf
        @method('PATCH')
        <input type="hidden" name="return" value="{{ $returnTo }}">

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="amount" class="label">Amount ({{ \App\Support\Money::symbol() }})</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required
                       value="{{ old('amount', $expense->amount) }}" class="field num font-mono !text-xl">
                @error('amount') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="date" class="label">Date</label>
                <input type="date" id="date" name="date" required
                       min="{{ $openRange[0] }}" max="{{ $openRange[1] }}"
                       value="{{ old('date', $expense->date->toDateString()) }}" class="field font-mono">
                @error('date') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="category_id" class="label">Category</label>
            <select id="category_id" name="category_id" class="field" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $expense->category_id) == $category->id)>
                        {{ $category->name }}{{ $category->isArchived() ? ' (archived)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="note" class="label">What was it?</label>
            <input type="text" id="note" name="note" maxlength="500"
                   value="{{ old('note', $expense->note) }}" class="field">
            @error('note') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            <button type="submit" form="delete-expense" class="link text-sm text-rust">Delete entry</button>
            <div class="flex gap-3">
                <a href="{{ $returnTo }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-pine">Save</button>
            </div>
        </div>
    </form>

    <form id="delete-expense" method="POST" action="{{ route('expenses.destroy', $expense) }}"
          onsubmit="return confirm('Delete this entry? You can bring it back from Recently deleted.')">
        @csrf
        @method('DELETE')
        <input type="hidden" name="return" value="{{ $returnTo }}">
    </form>
</div>
@endsection
