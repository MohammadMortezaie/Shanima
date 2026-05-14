@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-7">
                <p class="text-uppercase small fw-semibold mb-2 muted-copy">Admin dashboard</p>
                <h1 class="display-6 mb-3">Daily client progress at a glance</h1>
                <p class="muted-copy mb-0">
                    Review each client’s assigned items, see what has been completed, and spot missed routines quickly.
                </p>
            </div>
            <div class="col-lg-5">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="surface-card-tight p-3">
                    <label class="form-label fw-semibold" for="date">Check progress for date</label>
                    <div class="d-flex gap-2">
                        <input id="date" type="date" name="date" value="{{ $date->toDateString() }}" class="form-control">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="stats-grid mb-4">
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Clients</div>
                <div class="stat-value">{{ $totals['clients'] }}</div>
                <div class="muted-copy mt-2">Tracked for {{ $date->format('M d, Y') }}</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Tasks due</div>
                <div class="stat-value">{{ $totals['tasks'] }}</div>
                <div class="muted-copy mt-2">Programs and routines combined</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Completed</div>
                <div class="stat-value">{{ $totals['completed'] }}</div>
                <div class="muted-copy mt-2">Marked done by clients</div>
            </div>
        </div>
        <div>
            <div class="surface-card p-4 stat-card">
                <div class="stat-label mb-3">Fully done</div>
                <div class="stat-value">{{ $totals['fully_done'] }}</div>
                <div class="muted-copy mt-2">Clients with zero pending items</div>
            </div>
        </div>
    </section>

    <section class="surface-card table-shell">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 pt-4">
            <div>
                <h2 class="section-title mb-1">Client status</h2>
                <p class="muted-copy mb-0">Open any client to view the full day, add new items, or inspect completion times.</p>
            </div>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">Add client</a>
        </div>

        <div class="table-responsive px-4 pb-4 pt-3">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Premium</th>
                        <th>Total</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>Completion</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        @php($summary = $client->daily_summary)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $client->full_name }}</div>
                                <div class="small muted-copy">{{ $client->email }}</div>
                            </td>
                            <td>
                                @if ($client->is_premium)
                                    <span class="soft-badge">Premium</span>
                                @else
                                    <span class="small muted-copy">Standard</span>
                                @endif
                            </td>
                            <td>{{ $summary['total'] }}</td>
                            <td>{{ $summary['completed'] }}</td>
                            <td>
                                <span class="status-chip {{ $summary['pending'] === 0 ? 'done' : 'pending' }}">
                                    {{ $summary['pending'] }}
                                </span>
                            </td>
                            <td>{{ $summary['completion_rate'] }}%</td>
                            <td class="text-end">
                                <a href="{{ route('admin.clients.show', ['client' => $client, 'date' => $date->toDateString()]) }}" class="btn btn-outline-primary btn-sm">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 muted-copy">No clients yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
