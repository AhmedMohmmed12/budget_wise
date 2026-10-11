<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('categories.index', [
            'categories' => $request->user()->categories()->ordered()
                ->withCount(['expenses' => fn ($q) => $q->withTrashed()])
                ->get(),
            'colors' => config('budgetwise.colors'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate($this->rules($request));

        $user->categories()->create($data + [
            'position' => (int) $user->categories()->max('position') + 1,
        ]);

        return back()->with('success', "Added {$data['name']}.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validateWithBag("category{$category->id}", $this->rules($request, $category)));

        return back()->with('success', 'Saved.');
    }

    public function archive(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update(['archived_at' => $category->isArchived() ? null : now()]);

        return back()->with('success', $category->isArchived()
            ? "{$category->name} is archived. Old entries keep it; it won't show up when adding new ones."
            : "{$category->name} is back.");
    }

    public function move(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        $ordered = $request->user()->categories()->ordered()->get()->values();
        $index = $ordered->search(fn ($c) => $c->id === $category->id);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swap >= 0 && $swap < $ordered->count()) {
            $ids = $ordered->pluck('id')->all();
            [$ids[$index], $ids[$swap]] = [$ids[$swap], $ids[$index]];

            DB::transaction(function () use ($ids) {
                foreach ($ids as $position => $id) {
                    Category::whereKey($id)->update(['position' => $position]);
                }
            });
        }

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->isInUse()) {
            return back()->with('error', "{$category->name} has entries or budgets, so it can only be archived.");
        }

        $category->delete();

        return back()->with('success', "Deleted {$category->name}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?Category $category = null): array
    {
        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('categories', 'name')
                    ->where('user_id', $request->user()->id)
                    ->ignore($category?->id),
            ],
            'color' => ['required', Rule::in(config('budgetwise.colors'))],
        ];
    }
}
