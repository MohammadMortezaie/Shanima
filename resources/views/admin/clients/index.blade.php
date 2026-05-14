@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <p class="text-uppercase small fw-semibold mb-2 muted-copy">Client management</p>
                <h1 class="display-6 mb-2">Clients</h1>
                <p class="muted-copy mb-0">Manage each client profile, premium status, and access to their daily dashboard.</p>
            </div>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">Create client</a>
        </div>
    </section>

    <section class="surface-card table-shell">
        <div class="table-responsive px-4 py-4">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Full name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date of birth</th>
                        <th>Plan</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr>
                            <td class="fw-semibold">{{ $client->full_name }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone }}</td>
                            <td>{{ $client->date_of_birth?->format('M d, Y') }}</td>
                            <td>
                                @if ($client->is_premium)
                                    <span class="soft-badge">Premium</span>
                                @else
                                    <span class="small muted-copy">Standard</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-primary btn-sm">Open</a>
                                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-light btn-sm">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
