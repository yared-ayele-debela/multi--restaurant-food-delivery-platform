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
                            <h5 class="mb-1">Welcome back</h5>
                            <p class="text-muted mb-0">Sign in to continue to {{ $siteName }}.</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

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
                                    autofocus
                                >
                                @error('email')
                                <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label mb-0" for="password">Password</label>
                                    @if(Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-muted small">Forgot password?</a>
                                    @endif
                                </div>
                                <input
                                    type="password"
                                    class="form-control mt-2 @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >
                                @error('password')
                                <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="remember-check"
                                    name="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="remember-check">Remember me</label>
                            </div>

                            <button class="btn btn-primary w-100" type="submit">Log In</button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="fw-semibold">Sign up now</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.layouts.javascript')
