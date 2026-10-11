{{-- Quick add form. Expects $categories and $openRange. --}}
<form id="expenseForm" class="grid gap-5 sm:grid-cols-[8rem_minmax(0,1fr)_10rem]" novalidate>
    @csrf

    <div>
        <label for="amount" class="label">Amount ({{ \App\Support\Money::symbol() }})</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0.01"
               class="field num font-mono" placeholder="12.50" required>
        <p class="field-error hidden" data-error="amount"></p>
    </div>

    <div>
        <label for="note" class="label">What was it?</label>
        <input type="text" id="note" name="note" maxlength="500"
               class="field" placeholder="Lunch with Omar">
        <p class="field-error hidden" data-error="note"></p>
    </div>

    <div>
        <label for="date" class="label">Date</label>
        <input type="date" id="date" name="date" value="{{ $openRange[1] }}"
               min="{{ $openRange[0] }}" max="{{ $openRange[1] }}" class="field font-mono" required>
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
        <button type="submit" id="submitBtn" class="btn-pine w-full">Write it down</button>
    </div>

    <p id="formMessage" class="hidden text-sm sm:col-span-3"></p>
</form>

@push('scripts')
<script>
document.getElementById('expenseForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const button = document.getElementById('submitBtn');
    const message = document.getElementById('formMessage');

    form.querySelectorAll('[data-error]').forEach((el) => { el.textContent = ''; el.classList.add('hidden'); });
    message.className = 'hidden text-sm sm:col-span-3';
    button.disabled = true;
    button.textContent = 'Saving…';

    try {
        const response = await fetch('{{ route('expenses.store') }}', {
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
    button.textContent = 'Write it down';
});
</script>
@endpush
