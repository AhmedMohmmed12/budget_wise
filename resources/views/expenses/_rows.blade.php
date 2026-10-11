{{-- Ledger rows for a list of expenses. Expects $expenses. --}}
<table class="ledger">
    <tbody>
        @foreach ($expenses as $expense)
            @php $open = \App\Support\EntryWindow::isOpen($expense->date, Auth::user()); @endphp
            <tr>
                <td class="w-24 whitespace-nowrap font-mono text-ink-soft">{{ $expense->date->format($expense->date->isCurrentYear() ? 'M d' : "M d 'y") }}</td>
                <td>
                    <span class="mr-1.5 inline-block h-2 w-2 rounded-full align-middle" style="background: {{ $expense->category?->color ?? '#6b6457' }}"></span>
                    {{ $expense->note ?: $expense->category?->name }}
                    @if ($expense->note)
                        <span class="text-ink-soft">· {{ $expense->category?->name }}</span>
                    @endif
                </td>
                <td class="amount">@money($expense->amount)</td>
                <td class="w-24 whitespace-nowrap text-right text-sm">
                    @if ($open)
                        <a href="{{ route('expenses.edit', ['expense' => $expense, 'return' => request()->fullUrl()]) }}" class="link text-ink-soft">edit</a>
                        <button type="button" class="delete-expense ml-2 px-1 text-lg leading-none text-ink-soft hover:text-rust"
                                data-url="{{ route('expenses.destroy', $expense) }}" aria-label="Delete this entry" title="Delete">&times;</button>
                    @else
                        <span class="pill" title="This month is closed">closed</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@once
@push('scripts')
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('.delete-expense');
    if (!button || !confirm('Delete this entry? You can bring it back from Recently deleted.')) return;

    button.disabled = true;
    try {
        const response = await fetch(button.dataset.url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        if (!response.ok) throw new Error();
        location.reload();
    } catch {
        button.disabled = false;
        alert("Couldn't delete that entry. Try again.");
    }
});
</script>
@endpush
@endonce
