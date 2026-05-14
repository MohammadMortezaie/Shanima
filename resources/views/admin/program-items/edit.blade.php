@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <p class="text-uppercase small fw-semibold mb-2 muted-copy">Edit program item</p>
        <h1 class="display-6 mb-2">{{ $programItem->title }}</h1>
        <p class="muted-copy mb-0">Update the selected item for {{ $client->full_name }}.</p>
    </section>

    <section class="surface-card p-4 p-lg-5">
        @include('admin.program-items._form', [
            'action' => route('admin.clients.program-items.update', ['client' => $client, 'programItem' => $programItem]),
            'httpMethod' => 'PUT',
            'submitLabel' => 'Save item',
            'returnDate' => $returnDate,
        ])
    </section>
@endsection
