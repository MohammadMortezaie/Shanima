<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ProgramCompletion;
use App\Models\ProgramItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CompletionController extends Controller
{
    public function store(Request $request, ProgramItem $programItem): RedirectResponse
    {
        $date = $this->validatedDate($request, $programItem);

        ProgramCompletion::query()->updateOrCreate(
            [
                'program_item_id' => $programItem->id,
                'completion_date' => $date->toDateString(),
            ],
            [
                'user_id' => $request->user()->id,
                'completed_at' => now(),
            ],
        );

        return redirect()
            ->route('client.dashboard', $this->dashboardRouteParameters($request, $date))
            ->with('status', 'Marked as done.');
    }

    public function destroy(Request $request, ProgramItem $programItem): RedirectResponse
    {
        $date = $this->validatedDate($request, $programItem);

        ProgramCompletion::query()
            ->where('program_item_id', $programItem->id)
            ->where('user_id', $request->user()->id)
            ->whereDate('completion_date', $date->toDateString())
            ->delete();

        return redirect()
            ->route('client.dashboard', $this->dashboardRouteParameters($request, $date))
            ->with('status', 'Marked as not done.');
    }

    /**
     * @return array{category?: string, date: string}
     */
    private function dashboardRouteParameters(Request $request, Carbon $date): array
    {
        $parameters = [
            'date' => $date->toDateString(),
        ];

        $category = $request->string('category')->trim()->toString();

        if ($category !== '') {
            $parameters['category'] = $category;
        }

        return $parameters;
    }

    private function validatedDate(Request $request, ProgramItem $programItem): Carbon
    {
        abort_unless($programItem->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($data['date'])->startOfDay();

        if ($date->isFuture()) {
            throw ValidationException::withMessages([
                'date' => 'You can only update items for today or a past day.',
            ]);
        }

        if (! $programItem->isDueOn($date)) {
            abort(404);
        }

        return $date;
    }
}
