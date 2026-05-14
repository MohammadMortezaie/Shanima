<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Shanima') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell">
            <nav class="navbar navbar-expand-lg topbar sticky-top">
                <div class="container py-2">
                    <a class="navbar-brand d-flex align-items-center gap-3 fw-semibold" href="{{ route('dashboard') }}">
                        <span class="brand-mark">SH</span>
                        <span>Shanima</span>
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav" aria-controls="appNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="appNav">
                        <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                            @if (auth()->user()->isAdmin())
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                                <a class="nav-link" href="{{ route('admin.clients.index') }}">Clients</a>
                                <a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a>
                            @else
                                <a class="nav-link" href="{{ route('client.dashboard') }}">My Day</a>
                            @endif

                            <span class="soft-badge ms-lg-2">{{ auth()->user()->full_name }}</span>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm ms-lg-2">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="container py-4 py-lg-5">
                @if (session('status'))
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <div class="fw-semibold mb-2">Please fix the following:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
