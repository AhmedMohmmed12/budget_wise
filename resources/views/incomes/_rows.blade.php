{{-- Ledger rows for a list of income entries. Expects $incomes. --}}
<table class="ledger">
    <tbody>
        @foreach ($incomes as $income)
            @php $open = \App\Support\EntryWindow::isOpen($income->date, Auth::user()); @endphp
            <tr>
                <td class="w-24 whitespace-nowrap font-mono text-ink-soft">{{ $income->date->format($income->date->isCurrentYear() ? 'M d' : "M d 'y") }}</td>
                <td>
                    {{ $income->source }}
                    @if ($income->note) <span class="text-ink-soft">· {{ $income->note }}</span> @endif
                    @if ($income->recurring_entry_id) <span class="pill ml-1" title="Written down automatically">repeats</span> @endif
                </td>
                <td class="amount text-pine">+@money($income->amount)</td>
                <td class="w-24 whitespace-nowrap text-right text-sm">
                    @if ($open)
                        <a href="{{ route('incomes.edit', ['income' => $income, 'return' => request()->fullUrl()]) }}" class="link text-ink-soft">edit</a>
                        <button type="button" class="delete-entry ml-2 px-1 text-lg leading-none text-ink-soft hover:text-rust"
                                data-url="{{ route('incomes.destroy', $income) }}" aria-label="Delete this entry" title="Delete">&times;</button>
                    @else
                        <span class="pill" title="This month is closed">closed</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@include('partials.delete-entry-script')
