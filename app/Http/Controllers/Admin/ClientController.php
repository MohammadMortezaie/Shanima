<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProgramItem;
use App\Models\User;
use App\Services\DailyProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('admin.clients.index', [
            'clients' => User::query()
                ->clients()
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.clients.form', [
            'client' => new User([
                'is_premium' => false,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['role'] = User::ROLE_CLIENT;

        $client = User::query()->create($data);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', 'Client created successfully.');
    }

    public function show(Request $request, User $client, DailyProgramService $dailyProgramService): View
    {
        $this->ensureClient($client);

        $date = $dailyProgramService->resolveDate($request->string('date')->toString());
        $items = $dailyProgramService->itemsForDate($client, $date);

        return view('admin.clients.show', [
            'client' => $client,
            'date' => $date,
            'summary' => $dailyProgramService->summary($items),
            'items' => $items,
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'programItem' => new ProgramItem([
                'kind' => ProgramItem::KIND_CATEGORY_PROGRAM,
                'recurrence_type' => ProgramItem::RECURRENCE_ONCE,
                'content_type' => ProgramItem::CONTENT_TEXT,
                'scheduled_date' => $date->toDateString(),
                'is_active' => true,
            ]),
            'recentOneTimeItems' => ProgramItem::query()
                ->with('category')
                ->where('user_id', $client->id)
                ->where('recurrence_type', ProgramItem::RECURRENCE_ONCE)
                ->orderByDesc('scheduled_date')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function edit(User $client): View
    {
        $this->ensureClient($client);

        return view('admin.clients.form', [
            'client' => $client,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, User $client): RedirectResponse
    {
        $this->ensureClient($client);

        $data = $this->validatedData($request, $client);
        $client->update($data);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', 'Client details updated.');
    }

    private function ensureClient(User $client): void
    {
        abort_unless($client->isClient(), 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?User $client = null): array
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($client?->id),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'password' => [$client === null ? 'required' : 'nullable', 'string', 'min:8', 'max:255'],
            'is_premium' => ['nullable', 'boolean'],
        ]);

        $validated['is_premium'] = $request->boolean('is_premium');

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        return $validated;
    }
}
