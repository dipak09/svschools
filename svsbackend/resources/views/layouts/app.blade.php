<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="@yield('description', 'SV Schools - a simple school management system for students, teachers, attendance and fees.')">
  <title>@yield('title', 'SV Schools') | SV Schools</title>

  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/favicon.svg') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
  @stack('styles')
</head>
<body>

  <!-- ================= NAVBAR ================= -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="SV Schools logo">
        <span class="brand-text">SV Schools<small>Management System</small></span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#mainNav" aria-controls="mainNav"
              aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item">
            <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">Home</a>
          </li>
          @auth
            @if (auth()->user()->hasAnyRole(['staff', 'admin']))
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('students')) active @endif" href="{{ route('students') }}">Students</a>
              </li>
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('fees')) active @endif" href="{{ route('fees') }}">Fees</a>
              </li>
            @endif
          @endauth

          @auth
            @if (auth()->user()->canManageUsers())
              <li class="nav-item">
                <a class="nav-link @if(request()->routeIs('admin.*')) active @endif" href="{{ route('admin.dashboard') }}">Management</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">School modules</a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="{{ route('admin.students') }}">Students</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.teachers') }}">Teachers</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.parents') }}">Parents</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.classes') }}">Classes &amp; Sections</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.subjects') }}">Subjects</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.attendance') }}">Attendance</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.attendance.history') }}">Attendance history</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.academics') }}">Academics</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.announcements') }}">Announcements</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.reports') }}">Reports</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.activities') }}">Activity logs</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.roles') }}">Roles &amp; permissions</a></li>
                </ul>
              </li>
            @endif
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="nav-item dropdown ms-lg-2">
              <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userMenu"
                 role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar">{{ Str::of(auth()->user()->name)->substr(0, 2)->upper() }}</span>
                <span>{{ auth()->user()->name }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">Sign out</button>
                  </form>
                </li>
              </ul>
            </li>
          @else
            <li class="nav-item">
              <a class="nav-link @if(request()->routeIs('login')) active @endif" href="{{ route('login') }}">Login</a>
            </li>
            <li class="nav-item ms-lg-2">
              <a class="btn btn-sv" href="{{ route('register') }}">Register</a>
            </li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>

  @if (session('status'))
    <div class="container mt-3">
      <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @yield('content')

  <!-- ================= FOOTER ================= -->
  <footer class="site-footer">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="d-flex align-items-center gap-2 mb-3">
            <img src="{{ asset('assets/img/logo.svg') }}" alt="SV Schools logo" width="40" height="40">
            <span class="fw-bold text-white fs-5">SV Schools</span>
          </div>
          <p class="mb-0">A complete school management system built for administrators, teachers and parents.</p>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="mb-3">Pages</h6>
          <ul class="list-unstyled d-grid gap-2 mb-0">
            <li><a href="{{ route('home') }}">Home</a></li>
            @auth
              @if (auth()->user()->hasAnyRole(['staff', 'admin']))
                <li><a href="{{ route('students') }}">Students</a></li>
              @endif
            @endauth
            @guest
              <li><a href="{{ route('register') }}">Register</a></li>
            @endguest
          </ul>
        </div>
        <div class="col-6 col-lg-3">
          <h6 class="mb-3">Modules</h6>
          <ul class="list-unstyled d-grid gap-2 mb-0">
            <li><a href="{{ route('home') }}#features">Attendance</a></li>
            <li><a href="{{ route('home') }}#features">Fees &amp; Receipts</a></li>
            <li><a href="{{ route('home') }}#features">Exams &amp; Results</a></li>
          </ul>
        </div>
        <div class="col-lg-3">
          <h6 class="mb-3">Reach Us</h6>
          <ul class="list-unstyled d-grid gap-2 mb-0">
            <li>Station Road, Surat, Gujarat 395003</li>
            <li><a href="tel:+919876543210">+91 98765 43210</a></li>
            <li><a href="mailto:office@svschools.edu">office@svschools.edu</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom d-md-flex justify-content-between text-center text-md-start">
        <span>&copy; {{ date('Y') }} SV Schools. All rights reserved.</span>
        <span>Built with Laravel, Bootstrap 5 &amp; jQuery.</span>
      </div>
    </div>
  </footer>

  <button id="backToTop" type="button" aria-label="Back to top">&#8593;</button>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
