@extends('layouts.app')

@section('title', 'Edit recurring')

@section('content')
<div class="mx-auto max-w-xl">
    <p class="eyebrow"><a href="{{ route('recurring.index') }}" class="link">Recurring</a> · Edit</p>
    <h1 class="mt-1 text-3xl">{{ $entry->description }}</h1>
    <p class="mt-1 text-sm text-ink-soft">Changes apply from the next time it's written down.</p>

    <form method="POST" action="{{ route('recurring.update', $entry) }}" class="mt-8">
        @csrf
        @method('PATCH')
        @include('recurring._form', ['entry' => $entry])

        <div class="mt-8 flex items-center justify-between gap-3">
            <button type="submit" form="delete-recurring" class="link text-sm text-rust">Stop repeating</button>
            <div class="flex gap-3">
                <a href="{{ route('recurring.index') }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-pine">Save</button>
            </div>
        </div>
    </form>

    <form id="delete-recurring" method="POST" action="{{ route('recurring.destroy', $entry) }}"
          onsubmit="return confirm('Stop repeating {{ addslashes($entry->description) }}? Entries already written down stay.')">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
