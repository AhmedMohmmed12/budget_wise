@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="mx-auto max-w-2xl">
    <p class="eyebrow">Categories</p>
    <h1 class="mt-1 text-3xl">Where your money goes</h1>
    <p class="mt-2 text-ink-soft">
        Name them the way you think about spending. Archive the ones you're done with; old entries keep them.
    </p>

    {{-- Add --}}
    <form method="POST" action="{{ route('categories.store') }}" class="mt-8 grid gap-4 border-y border-ink py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-end"
          x-data="{ color: '{{ old('color', $colors[0]) }}' }">
        @csrf
        <div>
            <label for="new-name" class="label">New category</label>
            <input type="text" id="new-name" name="name" maxlength="50" value="{{ old('name') }}" placeholder="Groceries" class="field" required>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        @include('categories._swatches', ['id' => 'new'])
        <button type="submit" class="btn-pine">Add</button>
    </form>

    {{-- List --}}
    <ul class="mt-6">
        @foreach ($categories as $category)
            @php $bag = $errors->getBag("category{$category->id}"); @endphp
            <li class="border-b border-dotted border-rule py-3 {{ $category->isArchived() ? 'opacity-60' : '' }}"
                x-data="{ editing: {{ $bag->any() ? 'true' : 'false' }}, color: '{{ $bag->any() ? old('color', $category->color) : $category->color }}' }">
                <div class="flex items-center gap-3" x-show="!editing">
                    <span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $category->color }}"></span>
                    <span class="flex-1">
                        {{ $category->name }}
                        @if ($category->isArchived()) <span class="pill ml-1">archived</span> @endif
                        <span class="ml-1 font-mono text-xs text-ink-soft">{{ $category->expenses_count }} {{ \Illuminate\Support\Str::plural('entry', $category->expenses_count) }}</span>
                    </span>

                    <span class="flex items-center gap-3 text-sm">
                        <form method="POST" action="{{ route('categories.move', $category) }}" class="inline">
                            @csrf
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="px-1 text-ink-soft hover:text-ink disabled:opacity-30" @disabled($loop->first) aria-label="Move {{ $category->name }} up">&uarr;</button>
                        </form>
                        <form method="POST" action="{{ route('categories.move', $category) }}" class="inline">
                            @csrf
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="px-1 text-ink-soft hover:text-ink disabled:opacity-30" @disabled($loop->last) aria-label="Move {{ $category->name }} down">&darr;</button>
                        </form>
                        <button type="button" class="link text-ink-soft" @click="editing = true">edit</button>
                        <form method="POST" action="{{ route('categories.archive', $category) }}" class="inline">
                            @csrf
                            <button type="submit" class="link text-ink-soft">{{ $category->isArchived() ? 'unarchive' : 'archive' }}</button>
                        </form>
                        @if ($category->expenses_count === 0)
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline"
                                  onsubmit="return confirm('Delete {{ addslashes($category->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link text-rust">delete</button>
                            </form>
                        @endif
                    </span>
                </div>

                <form method="POST" action="{{ route('categories.update', $category) }}" x-show="editing" x-cloak
                      class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-end">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="name-{{ $category->id }}" class="label">Name</label>
                        <input type="text" id="name-{{ $category->id }}" name="name" maxlength="50" required class="field"
                               value="{{ $bag->any() ? old('name', $category->name) : $category->name }}">
                        @foreach ($bag->get('name') as $message) <p class="field-error">{{ $message }}</p> @endforeach
                    </div>
                    @include('categories._swatches', ['id' => $category->id])
                    <div class="flex gap-2">
                        <button type="button" class="btn-ghost !px-4" @click="editing = false">Cancel</button>
                        <button type="submit" class="btn-pine !px-4">Save</button>
                    </div>
                </form>
            </li>
        @endforeach
    </ul>
</div>
@endsection
