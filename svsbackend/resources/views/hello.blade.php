@extends('layouts.app')

@section('title', 'Hello')
@section('description', 'A simple example of a Laravel view on the SV Schools theme.')

@section('content')

  <!-- ================= PAGE HEAD ================= -->
  <section class="page-head">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Hello</li>
        </ol>
      </nav>
      <h1 class="mb-1">Hello, {{ $name }}!</h1>
      <p class="text-muted mb-0">A simple example of a Laravel view.</p>
    </div>
  </section>

  <!-- ================= GREETING ================= -->
  <section class="section">
    <div class="container">
      <div class="row g-4 justify-content-center">
        <div class="col-lg-7">
          <div class="form-card">
            <div class="d-flex align-items-center gap-3 mb-4">
              <span class="avatar">{{ Str::of($name)->substr(0, 2)->upper() }}</span>
              <div>
                <h5 class="fw-bold mb-0">Hello, {{ $name }}!</h5>
                <p class="text-muted small mb-0">Rendered by <code>resources/views/hello.blade.php</code></p>
              </div>
            </div>

            <form method="GET" action="{{ route('hello') }}" class="row g-3 align-items-end">
              <div class="col-sm-8">
                <label for="name" class="form-label">Say hello to someone else</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="{{ $name }}" maxlength="60" placeholder="Enter a name">
              </div>
              <div class="col-sm-4">
                <button type="submit" class="btn btn-sv w-100">Say hello</button>
              </div>
            </form>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="info-tile">
            <h6 class="fw-bold mb-3">How this page works</h6>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span class="text-muted">Route</span><strong><code>/hello</code></strong>
            </div>
            <div class="d-flex justify-content-between border-bottom py-2">
              <span class="text-muted">Query string</span><strong><code>?name={{ $name }}</code></strong>
            </div>
            <div class="d-flex justify-content-between py-2">
              <span class="text-muted">Layout</span><strong><code>layouts.app</code></strong>
            </div>
            <p class="text-muted small mb-0 mt-3">
              The name comes straight from the query string, falling back to a default
              when none is given.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= GYM BANNERS ================= -->
  @php($gymData = $gym_data ?? [])
  <section class="section pt-0">
    <div class="container">
      <div class="d-md-flex align-items-center justify-content-between mb-3">
        <div>
          <h2 class="section-title h4 mb-1">Gym Banners</h2>
          <p class="text-muted mb-0 small">
            Loaded with the <code>GymBanner</code> model and its <code>gym</code> relationship.
          </p>
        </div>
        <span class="badge text-bg-light text-primary px-3 py-2 mt-2 mt-md-0">
          {{ count($gymData) }} record(s)
        </span>
      </div>

      <div class="table-card">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>Gym ID</th>
                <th>Gym</th>
                <th>Banner</th>
                <th>Added</th>
                <th class="text-end">Link</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($gymData as $banner)
                <tr>
                  <td>{{ $banner->gym_id }}</td>
                  <td>
                    @if ($banner->gym)
                      <span class="avatar me-2">{{ Str::of($banner->gym->name)->substr(0, 2)->upper() }}</span>
                      <span>{{ $banner->gym->name }}</span>
                    @else
                      <span class="text-muted small">Gym not found</span>
                    @endif
                  </td>
                  <td class="text-muted small text-break">{{ $banner->banner }}</td>
                  <td class="text-muted small">{{ $banner->createdAt?->format('d M Y') ?? '-' }}</td>
                  <td class="text-end">
                    @if ($banner->link)
                      <a href="{{ $banner->link }}" class="btn btn-sm btn-sv-outline"
                         target="_blank" rel="noopener noreferrer">Open</a>
                    @else
                      <span class="text-muted small">-</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-5">
                    <p class="fw-semibold mb-1">No gym banners found.</p>
                    <p class="text-muted small mb-0">The <code>gym_banners</code> table is empty.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

@endsection
