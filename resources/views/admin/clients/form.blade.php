@extends('layouts.app')

@section('content')
    <section class="surface-card hero-panel p-4 p-lg-5 mb-4">
        <p class="text-uppercase small fw-semibold mb-2 muted-copy">Client profile</p>
        <h1 class="display-6 mb-2">{{ $isEdit ? 'Edit client' : 'Create client' }}</h1>
        <p class="muted-copy mb-0">Store the profile details that the admin needs to manage programs and daily routines.</p>
    </section>

    <section class="surface-card p-4 p-lg-5">
        <form method="POST" action="{{ $isEdit ? route('admin.clients.update', $client) : route('admin.clients.store') }}" class="row g-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-6">
                <label class="form-label" for="full_name">Full name</label>
                <input id="full_name" type="text" name="full_name" class="form-control" value="{{ old('full_name', $client->full_name) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $client->email) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="phone">Phone number</label>
                <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="date_of_birth">Date of birth</label>
                <input id="date_of_birth" type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $client->date_of_birth?->format('Y-m-d')) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password">
                    Password
                    @if ($isEdit)
                        <span class="muted-copy small">(leave blank to keep current password)</span>
                    @endif
                </label>
                <input id="password" type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
            </div>

            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check mt-md-4 pt-md-2">
                    <input type="hidden" name="is_premium" value="0">
                    <input class="form-check-input" type="checkbox" value="1" id="is_premium" name="is_premium" @checked(old('is_premium', $client->is_premium))>
                    <label class="form-check-label" for="is_premium">
                        Premium client
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Save changes' : 'Create client' }}</button>
                <a href="{{ $isEdit ? route('admin.clients.show', $client) : route('admin.clients.index') }}" class="btn btn-outline-primary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
