{{-- Quick add for expenses and income. Expects $categories and $openRange. --}}
<div x-data="{ kind: 'expense' }">
    <div class="mb-4 flex gap-5 text-sm">
        <button type="button" @click="kind = 'expense'" :class="kind === 'expense' ? 'font-bold underline decoration-pine decoration-2 underline-offset-4' : 'text-ink-soft'">Expense</button>
        <button type="button" @click="kind = 'income'" :class="kind === 'income' ? 'font-bold underline decoration-pine decoration-2 underline-offset-4' : 'text-ink-soft'">Income</button>
    </div>

    <form x-show="kind === 'expense'" data-quick-add data-action="{{ route('expenses.store') }}"
          class="grid gap-5 sm:grid-cols-[8rem_minmax(0,1fr)_10rem]" novalidate>
        @csrf
        <div>
            <label for="amount" class="label">Amount ({{ \App\Support\Money::symbol() }})</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" class="field num font-mono" placeholder="12.50" required>
            <p class="field-error hidden" data-error="amount"></p>
        </div>
        <div>
            <label for="note" class="label">What was it?</label>
            <input type="text" id="note" name="note" maxlength="500" class="field" placeholder="Lunch with Omar">
            <p class="field-error hidden" data-error="note"></p>
        </div>
        <div>
            <label for="date" class="label">Date</label>
            <input type="date" id="date" name="date" value="{{ $openRange[1] }}" min="{{ $openRange[0] }}" max="{{ $openRange[1] }}" class="field font-mono" required>
            <p class="field-error hidden" data-error="date"></p>
        </div>
        <div class="sm:col-span-2">
            <label for="category_id" class="label">Category</label>
            <select id="category_id" name="category_id" class="field" required>
                <option value="">Pick one</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <p class="field-error hidden" data-error="category_id"></p>
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn-pine w-full">Write it down</button>
        </div>
        <p data-message class="hidden text-sm sm:col-span-3"></p>
    </form>

    <form x-show="kind === 'income'" x-cloak data-quick-add data-action="{{ route('incomes.store') }}"
          class="grid gap-5 sm:grid-cols-[8rem_minmax(0,1fr)_10rem]" novalidate>
        @csrf
        <div>
            <label for="income-amount" class="label">Amount ({{ \App\Support\Money::symbol() }})</label>
            <input type="number" id="income-amount" name="amount" step="0.01" min="0.01" class="field num font-mono" placeholder="3000" required>
            <p class="field-error hidden" data-error="amount"></p>
        </div>
        <div>
            <label for="income-source" class="label">From</label>
            <input type="text" id="income-source" name="source" maxlength="100" class="field" placeholder="Salary" required>
            <p class="field-error hidden" data-error="source"></p>
        </div>
        <div>
            <label for="income-date" class="label">Date</label>
            <input type="date" id="income-date" name="date" value="{{ $openRange[1] }}" min="{{ $openRange[0] }}" max="{{ $openRange[1] }}" class="field font-mono" required>
            <p class="field-error hidden" data-error="date"></p>
        </div>
        <div class="sm:col-span-2">
            <label for="income-note" class="label">Note (optional)</label>
            <input type="text" id="income-note" name="note" maxlength="500" class="field" placeholder="October pay">
            <p class="field-error hidden" data-error="note"></p>
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn-pine w-full">Write it down</button>
        </div>
        <p data-message class="hidden text-sm sm:col-span-3"></p>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-quick-add]').forEach((form) => form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    const message = form.querySelector('[data-message]');
    const label = button.textContent;

    form.querySelectorAll('[data-error]').forEach((el) => { el.textContent = ''; el.classList.add('hidden'); });
    message.className = 'hidden text-sm sm:col-span-3';
    button.disabled = true;
    button.textContent = 'Saving…';

    try {
        const response = await fetch(form.dataset.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: new FormData(form),
        });
        const data = await response.json().catch(() => ({}));

        if (response.ok && data.success) {
            message.textContent = 'Added.';
            message.className = 'text-sm text-pine sm:col-span-3';
            setTimeout(() => location.reload(), 600);
            return;
        }

        if (response.status === 422 && data.errors) {
            Object.entries(data.errors).forEach(([field, errors]) => {
                const el = form.querySelector(`[data-error="${field}"]`);
                if (el) { el.textContent = errors[0]; el.classList.remove('hidden'); }
            });
        } else {
            throw new Error();
        }
    } catch {
        message.textContent = "Couldn't save that. Check your connection and try again.";
        message.className = 'text-sm text-rust sm:col-span-3';
    }

    button.disabled = false;
    button.textContent = label;
}));
</script>
@endpush
