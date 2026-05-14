<?php

namespace App\Services;

use App\Models\ProgramCompletion;
use App\Models\ProgramItem;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DailyProgramService
{
    public function resolveDate(?string $date): Carbon
    {
        return filled($date)
            ? Carbon::parse($date)->startOfDay()
            : Carbon::today();
    }

    /**
     * @return Collection<int, ProgramItem>
     */
    public function itemsForDate(User $user, CarbonInterface|string $date): Collection
    {
        $resolvedDate = Carbon::parse($date)->startOfDay();

        return ProgramItem::query()
            ->forUser($user)
            ->where('is_active', true)
            ->dueOnDate($resolvedDate)
            ->with([
                'category',
                'completions' => fn ($query) => $query->whereDate('completion_date', $resolvedDate->toDateString()),
            ])
            ->get()
            ->sortBy([
                fn (ProgramItem $item) => $item->kind === ProgramItem::KIND_ROUTINE ? 0 : 1,
                fn (ProgramItem $item) => $item->category?->sort_order ?? 999,
                fn (ProgramItem $item) => strtolower($item->title),
            ])
            ->values()
            ->map(function (ProgramItem $item) use ($resolvedDate): ProgramItem {
                /** @var ProgramCompletion|null $completion */
                $completion = $item->completionForDate($resolvedDate);

                $item->setAttribute('completion_snapshot', $completion);
                $item->setAttribute('is_completed_for_date', $completion !== null);

                return $item;
            });
    }

    /**
     * @param Collection<int, ProgramItem> $items
     * @return array<string, int|string|null>
     */
    public function summary(Collection $items): array
    {
        $completedItems = $items->filter(fn (ProgramItem $item) => (bool) $item->getAttribute('is_completed_for_date'));
        $lastCompletedAt = $completedItems
            ->map(fn (ProgramItem $item) => $item->getAttribute('completion_snapshot')?->completed_at)
            ->filter()
            ->sort()
            ->last();

        $total = $items->count();
        $completed = $completedItems->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => max($total - $completed, 0),
            'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'last_completed_at' => $lastCompletedAt,
        ];
    }
}
