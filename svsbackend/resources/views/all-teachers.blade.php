@extends('layouts.app')

@section('title', 'Teachers')
@section('description', 'Teacher directory - search, sort and manage teacher records at SV Schools.')

@section('content')

  <!-- ================= PAGE HEAD ================= -->
  <section class="page-head">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Teachers</li>
        </ol>
      </nav>
      <div class="d-md-flex align-items-center justify-content-between">
        <div>
          <h1 class="mb-1">Teacher Directory</h1>
          <p class="text-muted mb-0">Search, sort and manage enrolled teachers.</p>
        </div>
        @auth
          <button class="btn btn-sv mt-3 mt-md-0" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            + Add Teacher
          </button>
        @else
          <a class="btn btn-sv mt-3 mt-md-0" href="{{ route('login') }}">Sign in to add</a>
        @endauth
      </div>
    </div>
  </section>

  <!-- ================= DIRECTORY ================= -->
  <section class="section">
    <div class="container">

      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ $errors->first() }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <!-- Filters -->
      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <label for="searchInput" class="form-label">Search</label>
          <input type="search" class="form-control" id="searchInput"
                 placeholder="Name, roll no or email...">
        </div>
        <div class="col-lg-2 d-flex align-items-end">
          <button type="button" class="btn btn-sv-outline w-100" id="resetFilters">Reset</button>
        </div>
      </div>

      <p class="text-muted small">Showing <strong id="resultCount">{{ $techers->count() }}</strong> teacher(s).</p>

      <!-- Table -->
      <div class="table-card">
        <div class="table-responsive">
          <table class="table align-middle mb-0" id="studentTable">
            <thead>
              <tr>
                <th class="sortable">Roll <span class="arrow">&#9650;&#9660;</span></th>
                <th class="sortable">Name <span class="arrow">&#9650;&#9660;</span></th>
                <th class="sortable">Email <span class="arrow">&#9650;&#9660;</span></th>
                <th class="sortable">Registered <span class="arrow">&#9650;&#9660;</span></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($techers as $techer)
                <tr>
                  <td class="s-roll">{{ $techer->id }}</td>
                  <td>
                    <span class="avatar me-2">{{ Str::of($techer->name)->substr(0, 2)->upper() }}</span>
                    <span class="s-name">{{ $techer->name }}</span>
                  </td>
                  <td>{{ $techer->email }}</td>
                  <td>{{ $techer->created_at?->format('d M Y') ?? '-' }}</td>
                </tr>
              @empty
                <tr class="no-result-row">
                  <td colspan="4" class="text-center py-5">
                    <p class="fw-semibold mb-1">No teachers on record yet.</p>
                    <p class="text-muted small mb-0">Add the first teacher to build the directory.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @if ($techers->count())
          <div id="noResults" class="text-center py-5" style="display:none;">
            <p class="fw-semibold mb-1">No teachers match your search.</p>
            <p class="text-muted small mb-0">Try a different keyword or reset the filters.</p>
          </div>
        @endif
      </div>
    </div>
  </section>

  @auth
    <!-- ================= ADD TEACHER MODAL ================= -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form id="storeTeacherForm" method="POST" action="{{ route('techers.store') }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title" id="addTeacherLabel">Add New Teacher</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label for="teaName" class="form-label">Teacher Name</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror"
                         id="teaName" name="name" value="{{ old('name') }}" required minlength="3">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-12">
                  <label for="teaEmail" class="form-label">Email</label>
                  <input type="email" class="form-control @error('email') is-invalid @enderror"
                         id="teaEmail" name="email" value="{{ old('email') }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-12">
                  <label for="teaStd" class="form-label">Standard</label>
                  <input type="text" class="form-control @error('std') is-invalid @enderror"
                         id="teaStd" name="std" value="{{ old('std') }}" required>
                  @error('std')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  <div class="form-text">Minimum 8 characters. The teacher can change it after signing in.</div>
                </div>


            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-sv">Save Teacher</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endauth

@endsection

@push('scripts')
  <script>
    // Re-open the modal when the server sends validation errors back.
    @if ($errors->any() && old('email'))
      $(function () {
        var el = document.getElementById("addTeacherModal");
        if (el) { bootstrap.Modal.getOrCreateInstance(el).show(); }
      });
    @endif
  </script>
@endpush
