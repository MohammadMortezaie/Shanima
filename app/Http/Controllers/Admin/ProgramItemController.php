<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProgramItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramItemController extends Controller
{
    public function store(Request $request, User $client): RedirectResponse
    {
        $this->ensureClient($client);

        $data = $this->validatedData($request);
        $data['user_id'] = $client->id;
        $data['assigned_by'] = $request->user()->id;

        ProgramItem::query()->create($data);

        return redirect()
            ->route('admin.clients.show', [
                'client' => $client,
                'date' => $request->input('return_date', $this->defaultReturnDate($data)),
            ])
            ->with('status', 'Program item assigned.');
    }

    public function edit(User $client, ProgramItem $programItem): View
    {
        $this->ensureProgramItem($client, $programItem);

        return view('admin.program-items.edit', [
            'client' => $client,
            'programItem' => $programItem,
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'returnDate' => request('date', $this->defaultReturnDate($programItem->toArray())),
        ]);
    }

    public function update(Request $request, User $client, ProgramItem $programItem): RedirectResponse
    {
        $this->ensureProgramItem($client, $programItem);

        $programItem->update($this->validatedData($request));

        return redirect()
            ->route('admin.clients.show', [
                'client' => $client,
                'date' => $request->input('return_date', $this->defaultReturnDate($programItem->toArray())),
            ])
            ->with('status', 'Program item updated.');
    }

    public function destroy(Request $request, User $client, ProgramItem $programItem): RedirectResponse
    {
        $this->ensureProgramItem($client, $programItem);

        $programItem->delete();

        return redirect()
            ->route('admin.clients.show', [
                'client' => $client,
                'date' => $request->input('return_date', now()->toDateString()),
            ])
            ->with('status', 'Program item deleted.');
    }

    private function ensureClient(User $client): void
    {
        abort_unless($client->isClient(), 404);
    }

    private function ensureProgramItem(User $client, ProgramItem $programItem): void
    {
        $this->ensureClient($client);

        abort_unless($programItem->user_id === $client->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'kind' => [
                'required',
                Rule::in([ProgramItem::KIND_CATEGORY_PROGRAM, ProgramItem::KIND_ROUTINE]),
            ],
            'title' => ['required', 'string', 'max:120'],
            'content_type' => [
                'required',
                Rule::in([ProgramItem::CONTENT_TEXT, ProgramItem::CONTENT_VIDEO]),
            ],
            'content_body' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'recurrence_type' => [
                'required',
                Rule::in([ProgramItem::RECURRENCE_ONCE, ProgramItem::RECURRENCE_DAILY]),
            ],
            'scheduled_date' => ['nullable', 'date'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $kind = $request->input('kind');
            $contentType = $request->input('content_type');
            $recurrenceType = $request->input('recurrence_type');

            if (blank($request->input('category_id'))) {
                $validator->errors()->add('category_id', 'Choose a category for this item.');
            }

            if ($kind === ProgramItem::KIND_CATEGORY_PROGRAM && $recurrenceType !== ProgramItem::RECURRENCE_ONCE) {
                $validator->errors()->add('recurrence_type', 'Category-based programs must be assigned to a single day.');
            }

            if ($recurrenceType === ProgramItem::RECURRENCE_ONCE && blank($request->input('scheduled_date'))) {
                $validator->errors()->add('scheduled_date', 'Choose the date for this item.');
            }

            if ($recurrenceType === ProgramItem::RECURRENCE_DAILY && blank($request->input('starts_on'))) {
                $validator->errors()->add('starts_on', 'Choose the start date for the daily routine.');
            }

            if ($contentType === ProgramItem::CONTENT_TEXT && blank($request->input('content_body'))) {
                $validator->errors()->add('content_body', 'Add the text instructions for this item.');
            }

            if ($contentType === ProgramItem::CONTENT_VIDEO && blank($request->input('video_url'))) {
                $validator->errors()->add('video_url', 'Add a video URL for this item.');
            }
        });

        $data = $validator->validate();
        $data['is_active'] = $request->boolean('is_active');

        if ($data['content_type'] === ProgramItem::CONTENT_TEXT) {
            $data['content_body'] = $request->input('content_body');
            $data['video_url'] = null;
        } else {
            $data['content_body'] = null;
            $data['video_url'] = $request->input('video_url');
        }

        if ($data['recurrence_type'] === ProgramItem::RECURRENCE_ONCE) {
            $data['starts_on'] = null;
            $data['ends_on'] = null;
        } else {
            $data['scheduled_date'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function defaultReturnDate(array $data): string
    {
        $candidate = $data['scheduled_date'] ?? $data['starts_on'] ?? now()->toDateString();

        return Carbon::parse($candidate)->toDateString();
    }
}
