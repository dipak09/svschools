@extends('layouts.app')

@section('title', 'Register')
@section('description', 'Create an SV Schools management system account.')

@section('content')

  <!-- ================= PAGE HEAD ================= -->
  <section class="page-head">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Register</li>
        </ol>
      </nav>
      <h1 class="mb-1">Create your account</h1>
      <p class="text-muted mb-0">Takes less than a minute &middot; No card required</p>
    </div>
  </section>

  <!-- ================= REGISTER ================= -->
  <section class="auth-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <div class="auth-card">
            <div class="row g-0">

              <!-- Brand panel -->
              <div class="col-lg-5 d-none d-lg-block">
                <div class="auth-aside">
                  <h2 class="mb-3">Join SV Schools</h2>
                  <p class="mb-0">
                    Create an account to manage admissions, students and daily
                    school operations from a single dashboard.
                  </p>
                  <ul class="auth-points">
                    <li><span class="tick">&#10003;</span> One profile per student</li>
                    <li><span class="tick">&#10003;</span> Conflict-free timetables</li>
                    <li><span class="tick">&#10003;</span> Term invoices and part payments</li>
                    <li><span class="tick">&#10003;</span> Instant parent notifications</li>
                  </ul>
                </div>
              </div>

              <!-- Form -->
              <div class="col-lg-7">
                <div class="auth-body">
                  <h1 class="mb-1">Register</h1>
                  <p class="text-muted mb-4">Fill in your details to set up an account.</p>

                  @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      Please correct the highlighted fields below.
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                  @endif

                  <form method="POST" action="{{ route('register.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                      <label for="name" class="form-label">Full name</label>
                      <input type="text"
                             class="form-control @error('name') is-invalid @enderror"
                             id="name" name="name" value="{{ old('name') }}"
                             autocomplete="name" autofocus required>
                      @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    <div class="mb-3">
                      <label for="email" class="form-label">Email address</label>
                      <input type="email"
                             class="form-control @error('email') is-invalid @enderror"
                             id="email" name="email" value="{{ old('email') }}"
                             autocomplete="email" required>
                      @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                      <div class="form-text">We will send school notices to this address.</div>
                    </div>

                    <div class="row g-3 mb-3">
                      <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                          <input type="password"
                                 class="form-control @error('password') is-invalid @enderror"
                                 id="password" name="password"
                                 autocomplete="new-password" required>
                          <button class="btn toggle-password" type="button"
                                  data-target="password" aria-label="Show password">Show</button>
                          @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="form-text">Minimum 8 characters.</div>
                      </div>
                      <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <div class="input-group">
                          <input type="password" class="form-control"
                                 id="password_confirmation" name="password_confirmation"
                                 autocomplete="new-password" required>
                          <button class="btn toggle-password" type="button"
                                  data-target="password_confirmation" aria-label="Show password">Show</button>
                        </div>
                      </div>
                    </div>

                    <div class="form-check mb-4">
                      <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                             value="1" id="terms" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                      <label class="form-check-label" for="terms">
                        I agree to the school&rsquo;s data and privacy policy.
                      </label>
                      @error('terms')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>

                    <button type="submit" class="btn btn-sv">Create account</button>
                  </form>

                  <div class="auth-foot text-center">
                    Already registered?
                    <a href="{{ route('login') }}">Sign in instead</a>
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
