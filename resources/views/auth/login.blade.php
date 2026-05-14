@extends('layouts.auth')

@section('content')
    <div class="surface-card hero-panel p-4 p-lg-5">
        <div class="d-flex align-items-center gap-3 mb-4">
            <span class="brand-mark">SH</span>
            <div>
                <p class="text-uppercase small fw-semibold mb-1 muted-copy">Welcome</p>
                <h1 class="h3 mb-0">Sign in to Shanima</h1>
            </div>
        </div>

        <p class="muted-copy mb-4">
            Admin can manage clients, categories, daily routines, and completion tracking.
            Clients can open their daily plan and mark items done.
        </p>

        <form method="POST" action="{{ route('login.store') }}" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="col-12">
                <label class="form-label" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-control" required>
            </div>

            <div class="col-12 d-flex align-items-center justify-content-between">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" @checked(old('remember'))>
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary px-4">Login</button>
            </div>
        </form>

    </div>
@endsection
