@extends('layouts.app')

@section('title', 'Edit goal')

@section('content')
<div class="mx-auto max-w-xl">
    <p class="eyebrow"><a href="{{ route('goals.index') }}" class="link">Goals</a> · Edit</p>
    <h1 class="mt-1 text-3xl">{{ $goal->name }}</h1>

    <form method="POST" action="{{ route('goals.update', $goal) }}" class="mt-8 space-y-6">
        @csrf
        @method('PATCH')

        <div>
            <label for="name" class="label">Saving for</label>
            <input type="text" id="name" name="name" maxlength="100" required class="field" value="{{ old('name', $goal->name) }}">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="target_amount" class="label">Target ({{ \App\Support\Money::symbol() }})</label>
                <input type="number" id="target_amount" name="target_amount" step="0.01" min="1" required class="field num font-mono"
                       value="{{ old('target_amount', $goal->target_amount) }}">
                @error('target_amount') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="target_date" class="label">By (optional)</label>
                <input type="date" id="target_date" name="target_date" class="field font-mono"
                       value="{{ old('target_date', $goal->target_date?->toDateString()) }}">
                @error('target_date') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            <button type="submit" form="delete-goal" class="link text-sm text-rust">Delete goal</button>
            <div class="flex gap-3">
                <a href="{{ route('goals.index') }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-pine">Save</button>
            </div>
        </div>
    </form>

    <form id="delete-goal" method="POST" action="{{ route('goals.destroy', $goal) }}"
          onsubmit="return confirm('Delete {{ addslashes($goal->name) }} and its history? The money stays wherever it actually is.')">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
