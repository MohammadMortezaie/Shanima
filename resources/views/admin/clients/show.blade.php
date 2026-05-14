@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <p class="text-uppercase small fw-semibold mb-0 muted-copy">Client detail</p>
                    @if ($client->is_premium)
                        <span class="soft-badge">Premium</span>
                    @endif
                </div>
                <h1 class="display-6 mb-2">{{ $client->full_name }}</h1>
                <div class="muted-copy">
                    {{ $client->email }} • {{ $client->phone }} • {{ $client->date_of_birth?->format('M d, Y') }}
                </div>
            </div>
            <div class="col-lg-5">
                <form method="GET" action="{{ route('admin.clients.show', $client) }}" class="surface-card-tight p-3">
                    <label class="form-label fw-semibold" for="date">Inspect day</label>
                    <div class="d-flex gap-2">
                        <input id="date" type="date" name="date" class="form-control" value="{{ $date->toDateString() }}">
                        <button type="submit" class="btn btn-primary">View</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="stats-grid mb-4">
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Due today</div>
                <div class="stat-value">{{ $summary['total'] }}</div>
                <div class="muted-copy mt-2">{{ $date->format('M d, Y') }}</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Completed</div>
                <div class="stat-value">{{ $summary['completed'] }}</div>
                <div class="muted-copy mt-2">Marked done by client</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Pending</div>
                <div class="stat-value">{{ $summary['pending'] }}</div>
                <div class="muted-copy mt-2">Still missing for the day</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Progress</div>
                <div class="stat-value">{{ $summary['completion_rate'] }}%</div>
                <div class="muted-copy mt-2">
                    @if ($summary['last_completed_at'])
                        Last done at {{ $summary['last_completed_at']->format('g:i A') }}
                    @else
                        No completions yet
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-xl-8">
            <div class="surface-card table-shell mb-4">
                <div class="px-4 pt-4">
                    <h2 class="section-title mb-1">Items due on {{ $date->format('M d, Y') }}</h2>
                    <p class="muted-copy mb-0">This view shows exactly what the client needed to complete for the selected date.</p>
                </div>

                <div class="table-responsive px-4 pb-4 pt-3">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Delivery</th>
                                <th>Status</th>
                                <th>Done at</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php($completion = $item->getAttribute('completion_snapshot'))
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->title }}</div>
                                        @if ($item->content_body)
                                            <div class="small muted-copy">{{ \Illuminate\Support\Str::limit($item->content_body, 90) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2 justify-content-start">
                                            @if ($item->kind === \App\Models\ProgramItem::KIND_ROUTINE)
                                                <span class="soft-badge">Routine</span>
                                            @endif

                                            <span class="soft-badge {{ $item->kind === \App\Models\ProgramItem::KIND_ROUTINE ? 'soft-badge-success' : '' }}">
                                                {{ $item->category?->name ?? 'Category' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ ucfirst($item->content_type) }}</td>
                                    <td>
                                        <span class="status-chip {{ $completion ? 'done' : 'pending' }}">
                                            {{ $completion ? 'Done' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td>{{ $completion?->completed_at?->format('g:i A') ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.clients.program-items.edit', ['client' => $client, 'programItem' => $item, 'date' => $date->toDateString()]) }}" class="btn btn-light btn-sm">Edit</a>
                                            <form method="POST" action="{{ route('admin.clients.program-items.destroy', ['client' => $client, 'programItem' => $item]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_date" value="{{ $date->toDateString() }}">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 muted-copy">No items are scheduled for this date.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="surface-card table-shell">
                <div class="px-4 pt-4">
                    <h2 class="section-title mb-1">Recent one-day assignments</h2>
                    <p class="muted-copy mb-0">Quick reference for recent date-specific programs.</p>
                </div>
                <div class="table-responsive px-4 pb-4 pt-3">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOneTimeItems as $recentItem)
                                <tr>
                                    <td>{{ $recentItem->scheduled_date?->format('M d, Y') }}</td>
                                    <td class="fw-semibold">{{ $recentItem->title }}</td>
                                    <td>{{ $recentItem->category?->name ?? 'Routine' }}</td>
                                    <td>{{ ucfirst($recentItem->content_type) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 muted-copy">No one-day assignments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="surface-card p-4">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h2 class="section-title mb-1">Assign new item</h2>
                        <p class="muted-copy mb-0">Use this for date-based programs or recurring routines. Both can be assigned to Morning, Evening, Night, and other categories.</p>
                    </div>
                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-outline-primary btn-sm">Edit client</a>
                </div>

                @include('admin.program-items._form', [
                    'action' => route('admin.clients.program-items.store', $client),
                    'httpMethod' => 'POST',
                    'submitLabel' => 'Assign item',
                    'returnDate' => $date->toDateString(),
                ])
            </div>
        </div>
    </section>
@endsection
