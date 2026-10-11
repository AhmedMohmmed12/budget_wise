{{-- Fields for a recurring entry. Expects $categories and optional $entry. --}}
@php $entry ??= null; @endphp
<div x-data="{ kind: '{{ old('kind', $entry->kind ?? 'expense') }}' }" class="space-y-5">
    <div class="flex gap-6 text-[15px]">
        <label class="flex items-center gap-2">
            <input type="radio" name="kind" value="expense" x-model="kind" class="border-ink text-pine focus:ring-pine"> Expense
        </label>
        <label class="flex items-center gap-2">
            <input type="radio" name="kind" value="income" x-model="kind" class="border-ink text-pine focus:ring-pine"> Income
        </label>
    </div>

    <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_8rem]">
        <div>
            <label for="description" class="label" x-text="kind === 'income' ? 'From' : 'What is it?'">What is it?</label>
            <input type="text" id="description" name="description" maxlength="100" required class="field"
                   value="{{ old('description', $entry->description ?? '') }}" :placeholder="kind === 'income' ? 'Salary' : 'Rent'">
            @error('description') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="r-amount" class="label">Amount ({{ \App\Support\Money::symbol() }})</label>
            <input type="number" id="r-amount" name="amount" step="0.01" min="0.01" required class="field num font-mono"
                   value="{{ old('amount', $entry->amount ?? '') }}">
            @error('amount') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div x-show="kind === 'expense'">
            <label for="r-category" class="label">Category</label>
            <select id="r-category" name="category_id" class="field">
                <option value="">Pick one</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $entry->category_id ?? 0) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="day_of_month" class="label">On day</label>
            <select id="day_of_month" name="day_of_month" class="field font-mono">
                @for ($day = 1; $day <= 31; $day++)
                    <option value="{{ $day }}" @selected((int) old('day_of_month', $entry->day_of_month ?? 1) === $day)>{{ $day }}{{ $day > 28 ? ' (or the last day)' : '' }}</option>
                @endfor
            </select>
            @error('day_of_month') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="starts_on" class="label">Starting</label>
            <input type="date" id="starts_on" name="starts_on" required class="field font-mono"
                   value="{{ old('starts_on', $entry?->starts_on?->toDateString() ?? \App\Support\EntryWindow::today(Auth::user())->startOfMonth()->toDateString()) }}">
            <p class="mt-1 text-xs text-ink-soft">If this month's day has already passed, it's written down right away.</p>
            @error('starts_on') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="ends_on" class="label">Ending (optional)</label>
            <input type="date" id="ends_on" name="ends_on" class="field font-mono"
                   value="{{ old('ends_on', $entry?->ends_on?->toDateString()) }}">
            @error('ends_on') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
