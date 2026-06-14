@extends('layouts.auth')

@section('content')
<div class="p-4 p-sm-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark mb-1">PPeC <span style="color: var(--app-red);">Lanka</span></h2>
        <p class="text-muted small">eeal Request Portal</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label small fw-bold text-secondary">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                <input type="text" name="username" id="username" class="form-control bg-light border-start-0 ps-0" placeholder="Enter your username" value="{{ old('username') }}" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label small fw-bold text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control bg-light border-start-0 ps-0" placeholder="Enter your password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 text-uppercase fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px;">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </button>
    </form>
</div>
@endsection
