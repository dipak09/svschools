@extends('layouts.app')

@section('title', 'Dashboard')
@section('description', 'Your SV Schools dashboard.')

@section('content')

  <!-- ================= PAGE HEAD ================= -->
  <section class="page-head">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
      </nav>
      <h1 class="mb-1">Hello, {{ $user->name }}</h1>
      <p class="text-muted mb-0">{{ $user->email }} &middot; Session 2026 - 27</p>
    </div>
  </section>

  <!-- ================= TILES ================= -->
  <section class="section">
    <div class="container">
      <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
          <div class="info-tile">
            <div class="stat-value">{{ number_format($studentCount) }}</div>
            <div class="stat-label">Students</div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="info-tile">
            <div class="stat-value">18 / 22</div>
            <div class="stat-label">Attendance marked</div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="info-tile">
            <div class="stat-value">&#8377; {{ number_format($collectedFees, 2) }}</div>
            <div class="stat-label">Fees collected</div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="info-tile">
            <div class="stat-value">14</div>
            <div class="stat-label">New applications</div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="form-card">
            <h5 class="fw-bold mb-3">Quick actions</h5>
            <div class="d-flex flex-wrap gap-3">
              @if ($user->hasAnyRole(['staff', 'admin']))
                <a href="{{ route('students') }}" class="btn btn-sv">View all students</a>
                <a href="{{ route('fees') }}" class="btn btn-sv-outline">Manage fees</a>
              @endif
              <a href="{{ route('home') }}#features" class="btn btn-sv-outline">Browse modules</a>
              <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Sign out</button>
              </form>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="info-tile">
            <h6 class="fw-bold mb-3">Your account</h6>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span class="text-muted">Name</span><strong>{{ $user->name }}</strong>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span class="text-muted">Email</span><strong>{{ $user->email }}</strong>
            </div>
            <div class="d-flex justify-content-between py-2">
              <span class="text-muted">Joined</span><strong>{{ $user->created_at?->format('d M Y') ?? '-' }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

@endsection
