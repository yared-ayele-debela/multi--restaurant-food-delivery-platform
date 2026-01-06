@php
    $siteSettings = \App\Models\Setting::getInstance();
    $siteName = $siteSettings->site_name ?: 'Food Delivery';
    $logoUrl = $siteSettings->getLogoUrl();
@endphp
@include('admin.layouts.css')
<div class="auth-page min-vh-100 d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-5 col-md-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-sm-5">
                        <div class="text-center mb-4">
                            <a href="{{ url('/') }}" class="d-inline-flex align-items-center justify-content-center mb-3">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height: 44px;">
                                @else
                                    <img src="{{ asset('admin/dist/assets/images/logo-sm.svg') }}" alt="{{ $siteName }}" height="30">
                                @endif
                            </a>
                            <h5 class="mb-1">Create account</h5>
                            <p class="text-muted mb-0">Sign up to join {{ $siteName }}.</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="name">Name</label>
                                <input
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    placeholder="Enter your name"
                                    value="{{ old('name') }}"
                                    required
                                    autofocus
                                >
                                @error('name')
                                <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    value="{{ old('email') }}"
                                    required
                                >
                                @error('email')
                                <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="Enter password"
                                    required
                                >
                                @error('password')
                                <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="password_confirmation">Confirm password</label>
                                <input
                                    type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                    required
                                >
                                @error('password_confirmation')
                                <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Already registered?
                            <a href="{{ route('login') }}" class="fw-semibold">Log in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.layouts.javascript')
