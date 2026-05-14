<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProgramItem;
use App\Services\DailyProgramService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DailyProgramService $dailyProgramService): View
    {
        $date = $dailyProgramService->resolveDate($request->string('date')->toString());
        $items = $dailyProgramService->itemsForDate($request->user(), $date);
        $selectedCategorySlug = $request->string('category')->trim()->toString();
        $categorizedItems = $items
            ->filter(fn (ProgramItem $item) => $item->category_id !== null)
            ->values();
        $categoryFilters = $this->categoryFilters($categorizedItems);

        if ($selectedCategorySlug !== '' && ! $categoryFilters->contains(fn (array $filter) => $filter['slug'] === $selectedCategorySlug)) {
            $selectedCategorySlug = '';
        }

        $selectedCategory = $categoryFilters->firstWhere('slug', $selectedCategorySlug);

        $filteredItems = $selectedCategorySlug === ''
            ? $items
            : $items
                ->filter(fn (ProgramItem $item) => $item->category?->slug === $selectedCategorySlug)
                ->values();

        return view('client.dashboard', [
            'date' => $date,
            'summary' => $dailyProgramService->summary($items),
            'routines' => $filteredItems
                ->where('kind', ProgramItem::KIND_ROUTINE)
                ->values(),
            'categoryGroups' => $this->categoryGroups(
                $filteredItems
                    ->where('kind', ProgramItem::KIND_CATEGORY_PROGRAM)
                    ->values()
            ),
            'categoryFilters' => $categoryFilters,
            'selectedCategorySlug' => $selectedCategorySlug,
            'selectedCategoryLabel' => $selectedCategory['name'] ?? null,
            'isFutureDate' => $date->isFuture(),
        ]);
    }

    /**
     * @param  Collection<int, ProgramItem>  $items
     * @return Collection<int, array{count: int, name: string, slug: string}>
     */
    private function categoryFilters(Collection $items): Collection
    {
        $counts = $items->countBy(fn (ProgramItem $item) => (string) $item->category_id);

        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'count' => (int) ($counts->get((string) $category->id) ?? 0),
                'name' => $category->name,
                'slug' => $category->slug,
            ]);
    }

    /**
     * @param  Collection<int, ProgramItem>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function categoryGroups(Collection $items): Collection
    {
        return $items
            ->groupBy(fn (ProgramItem $item) => $item->category_id ?? 0)
            ->map(function (Collection $group): array {
                $first = $group->first();

                return [
                    'label' => $first?->category?->name ?? 'Uncategorized',
                    'sort_order' => $first?->category?->sort_order ?? 999,
                    'items' => $group->values(),
                ];
            })
            ->sortBy('sort_order')
            ->values();
    }
}
