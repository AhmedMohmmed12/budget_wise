<?php

namespace App\Http\Controllers;

use App\Support\EntryWindow;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The full history: filter, page through and export entries.
 */
class EntryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $this->filters($request);

        $query = $this->filtered($request, $filters);
        $total = (clone $query)->sum('amount');

        if ($filters['type'] === 'expenses') {
            $query->with('category');
        }

        return view('entries.index', [
            'filters' => $filters,
            'entries' => $query->orderByDesc('date')->orderByDesc('id')->paginate(25)->withQueryString(),
            'total' => (float) $total,
            'categories' => $user->categories()->ordered()->get(),
            'thisMonth' => EntryWindow::today($user)->format('Y-m'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $filters = $this->filters($request);
        $isIncome = $filters['type'] === 'income';

        $rows = $this->filtered($request, $filters)->orderBy('date')->orderBy('id');
        if (! $isIncome) {
            $rows->with('category');
        }

        $name = 'budgetwise-'.$filters['type'].'-'.$filters['month'].'.csv';

        return response()->streamDownload(function () use ($rows, $user, $isIncome) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', $isIncome ? 'Source' : 'Category', 'Note', 'Amount', 'Currency']);

            $rows->chunk(500, function ($chunk) use ($out, $user, $isIncome) {
                foreach ($chunk as $entry) {
                    fputcsv($out, [
                        $entry->date->toDateString(),
                        $this->csvSafe($isIncome ? $entry->source : $entry->category?->name),
                        $this->csvSafe($entry->note),
                        number_format((float) $entry->amount, 2, '.', ''),
                        $user->currency,
                    ]);
                }
            });

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{type: string, month: string, category: ?int, q: ?string, min: ?float, max: ?float}
     */
    private function filters(Request $request): array
    {
        $month = $request->query('month');
        if ($month === null && $request->query->has('month')) {
            $month = 'all'; // the month box was cleared
        }
        if ($month !== 'all' && ! (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month))) {
            $month = EntryWindow::today($request->user())->format('Y-m');
        }

        $number = fn ($key) => is_numeric($request->query($key)) ? (float) $request->query($key) : null;

        return [
            'type' => $request->query('type') === 'income' ? 'income' : 'expenses',
            'month' => $month,
            'category' => is_numeric($request->query('category')) ? (int) $request->query('category') : null,
            'q' => trim((string) $request->query('q')) ?: null,
            'min' => $number('min'),
            'max' => $number('max'),
        ];
    }

    private function filtered(Request $request, array $filters): Builder
    {
        $isIncome = $filters['type'] === 'income';
        $query = ($isIncome ? $request->user()->incomes() : $request->user()->expenses())->getQuery();

        if ($filters['month'] !== 'all') {
            $start = CarbonImmutable::parse($filters['month'].'-01');
            $query->whereBetween('date', [$start->toDateString(), $start->endOfMonth()->toDateString()]);
        }

        if ($filters['q']) {
            $like = '%'.addcslashes($filters['q'], '%_\\').'%';
            $query->where(fn ($q) => $isIncome
                ? $q->where('source', 'like', $like)->orWhere('note', 'like', $like)
                : $q->where('note', 'like', $like));
        }

        return $query
            ->when(! $isIncome && $filters['category'], fn ($q) => $q->where('category_id', $filters['category']))
            ->when($filters['min'] !== null, fn ($q) => $q->where('amount', '>=', $filters['min']))
            ->when($filters['max'] !== null, fn ($q) => $q->where('amount', '<=', $filters['max']));
    }

    /**
     * Stop spreadsheet apps from treating cells as formulas.
     */
    private function csvSafe(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
