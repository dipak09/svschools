@extends('layouts.app')

@section('title', 'Login')
@section('description', 'Sign in to the SV Schools management system.')

@section('content')

  <!-- ================= PAGE HEAD ================= -->
  <section class="page-head">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Login</li>
        </ol>
      </nav>
      <h1 class="mb-1">Sign in to your account</h1>
      <p class="text-muted mb-0">Staff, teachers and parents &middot; Session 2026 - 27</p>
    </div>
  </section>

  <!-- ================= LOGIN ================= -->
  <section class="auth-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <div class="auth-card">
            <div class="row g-0">

              <!-- Brand panel -->
              <div class="col-lg-5 d-none d-lg-block">
                <div class="auth-aside">
                  <h2 class="mb-3">Welcome back</h2>
                  <p class="mb-0">
                    One login for admissions, student records, attendance, timetables
                    and fee collection.
                  </p>
                  <ul class="auth-points">
                    <li><span class="tick">&#10003;</span> Mark attendance in seconds</li>
                    <li><span class="tick">&#10003;</span> Track fees and print receipts</li>
                    <li><span class="tick">&#10003;</span> Publish results to parents</li>
                    <li><span class="tick">&#10003;</span> Broadcast circulars to any class</li>
                  </ul>
                </div>
              </div>

              <!-- Form -->
              <div class="col-lg-7">
                <div class="auth-body">
                  <h1 class="mb-1">Login</h1>
                  <p class="text-muted mb-4">Enter your registered email and password to continue.</p>

                  @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      {{ $errors->first() }}
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                  @endif

                  <form method="POST" action="{{ route('login.attempt') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                      <label for="email" class="form-label">Email address</label>
                      <input type="email"
                             class="form-control @error('email') is-invalid @enderror"
                             id="email" name="email" value="{{ old('email') }}"
                             autocomplete="email" autofocus required>
                      @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                      <div class="input-group">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password"
                               autocomplete="current-password" required>
                        <button class="btn toggle-password" type="button"
                                data-target="password" aria-label="Show password">Show</button>
                        @error('password')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1"
                               id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Keep me signed in</label>
                      </div>
                    </div>

                    <button type="submit" class="btn btn-sv">Sign in</button>
                  </form>

                  <div class="auth-foot text-center">
                    New to SV Schools?
                    <a href="{{ route('register') }}">Create an account</a>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection
