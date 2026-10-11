<?php

namespace App\Http\Controllers;

use App\Support\EntryWindow;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

        $query = $this->filtered($user->expenses(), $filters);
        $total = (clone $query)->sum('amount');

        return view('entries.index', [
            'filters' => $filters,
            'expenses' => $query->with('category')->orderByDesc('date')->orderByDesc('id')->paginate(25)->withQueryString(),
            'total' => (float) $total,
            'categories' => $user->categories()->ordered()->get(),
            'thisMonth' => EntryWindow::today($user)->format('Y-m'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $filters = $this->filters($request);
        $rows = $this->filtered($user->expenses(), $filters)->with('category')->orderBy('date')->orderBy('id');

        $name = 'budgetwise-expenses-'.($filters['month'] === 'all' ? 'all' : $filters['month']).'.csv';

        return response()->streamDownload(function () use ($rows, $user) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Category', 'Note', 'Amount', 'Currency']);

            $rows->chunk(500, function ($chunk) use ($out, $user) {
                foreach ($chunk as $expense) {
                    fputcsv($out, [
                        $expense->date->toDateString(),
                        $expense->category?->name,
                        $this->csvSafe($expense->note),
                        number_format((float) $expense->amount, 2, '.', ''),
                        $user->currency,
                    ]);
                }
            });

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{month: string, category: ?int, q: ?string, min: ?float, max: ?float}
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
            'month' => $month,
            'category' => is_numeric($request->query('category')) ? (int) $request->query('category') : null,
            'q' => trim((string) $request->query('q')) ?: null,
            'min' => $number('min'),
            'max' => $number('max'),
        ];
    }

    private function filtered(HasMany $relation, array $filters): Builder
    {
        $query = $relation->getQuery();

        if ($filters['month'] !== 'all') {
            $start = CarbonImmutable::parse($filters['month'].'-01');
            $query->whereBetween('date', [$start->toDateString(), $start->endOfMonth()->toDateString()]);
        }

        return $query
            ->when($filters['category'], fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['q'], fn ($q, $text) => $q->where('note', 'like', '%'.addcslashes($text, '%_\\').'%'))
            ->when($filters['min'] !== null, fn ($q) => $q->where('amount', '>=', $filters['min']))
            ->when($filters['max'] !== null, fn ($q) => $q->where('amount', '<=', $filters['max']));
    }

    /**
     * Stop spreadsheet apps from treating notes as formulas.
     */
    private function csvSafe(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
