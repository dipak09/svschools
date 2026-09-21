@extends('layouts.app')

@section('title', 'School Management System')

@section('content')

  <!-- ================= HERO ================= -->
  <header class="hero">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-7">
          <span class="badge text-bg-light text-primary mb-3 px-3 py-2">Academic Session 2026 - 27</span>
          <h1 class="mb-3">Run your entire school from one dashboard</h1>
          <p class="lead mb-4">
            SV Schools brings admissions, student records, attendance, timetables and
            fee collection together, so your staff spends less time on paperwork and
            more time teaching.
          </p>
          <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
            @auth
              <a href="{{ route('dashboard') }}" class="btn btn-light btn-sv-outline bg-white">Go to Dashboard</a>
              @if (auth()->user()->hasAnyRole(['staff', 'admin']))
                <a href="{{ route('students') }}" class="btn btn-light px-4 fw-semibold">View Students</a>
              @endif
            @else
              <a href="{{ route('login') }}" class="btn btn-light btn-sv-outline bg-white">Sign in</a>
              <a href="{{ route('register') }}" class="btn btn-light px-4 fw-semibold">Create an account</a>
            @endauth
          </div>
        </div>
        <div class="col-lg-5">
          <div class="bg-white text-dark rounded-4 p-4 shadow-lg">
            <h6 class="text-uppercase text-muted small fw-bold mb-3">Today at a glance</h6>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span>Attendance marked</span><strong class="text-primary">18 / 22 classes</strong>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span>Fees collected</span><strong class="text-primary">&#8377; 2,45,000</strong>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span>New applications</span><strong class="text-primary">14</strong>
            </div>
            <div class="d-flex justify-content-between py-2">
              <span>Staff on leave</span><strong class="text-primary">3</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ================= STATS ================= -->
  <section class="stats py-5">
    <div class="container">
      <div class="row text-center g-4">
        <div class="col-6 col-lg-3">
          <div class="stat-value counter" data-target="1450">0</div>
          <div class="stat-label">Students</div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-value counter" data-target="86">0</div>
          <div class="stat-label">Teachers</div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-value counter" data-target="42">0</div>
          <div class="stat-label">Classrooms</div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="stat-value counter" data-target="25">0</div>
          <div class="stat-label">Years of Service</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= FEATURES ================= -->
  <section class="section" id="features">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title mb-2">Everything the office needs</h2>
        <p class="section-sub">Six modules that cover the daily running of a school, from the front desk to the report card.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon">&#128100;</div>
            <h5 class="fw-bold">Student Records</h5>
            <p class="text-muted mb-0">One profile per student - personal details, guardians, documents and academic history in a single place.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon">&#128197;</div>
            <h5 class="fw-bold">Attendance</h5>
            <p class="text-muted mb-0">Class teachers mark attendance in seconds and parents get an SMS the moment a child is absent.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon">&#128176;</div>
            <h5 class="fw-bold">Fees &amp; Receipts</h5>
            <p class="text-muted mb-0">Generate term invoices, accept part payments and print receipts without touching a spreadsheet.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon">&#128218;</div>
            <h5 class="fw-bold">Exams &amp; Results</h5>
            <p class="text-muted mb-0">Enter marks subject-wise and publish grade-wise report cards to the parent portal instantly.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon">&#128336;</div>
            <h5 class="fw-bold">Timetable</h5>
            <p class="text-muted mb-0">Build conflict-free period plans and reassign substitute teachers with a drag of the mouse.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon">&#128172;</div>
            <h5 class="fw-bold">Parent Notices</h5>
            <p class="text-muted mb-0">Broadcast circulars, holiday lists and PTM invites to any class or the whole school at once.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= CTA ================= -->
  <section class="section pt-0">
    <div class="container">
      <div class="rounded-4 p-4 p-md-5 text-center" style="background: var(--sv-surface); border: 1px solid var(--sv-border);">
        @auth
          <h3 class="fw-bold mb-2">You are signed in</h3>
          <p class="text-muted mb-4">Head to your dashboard to review attendance, fees and new applications.</p>
          <a href="{{ route('dashboard') }}" class="btn btn-sv">Open Dashboard</a>
        @else
          <h3 class="fw-bold mb-2">Admissions for 2026 - 27 are open</h3>
          <p class="text-muted mb-4">Register an account to submit an application and track its progress online.</p>
          <a href="{{ route('register') }}" class="btn btn-sv">Create an account</a>
        @endauth
      </div>
    </div>
  </section>

@endsection
