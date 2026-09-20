@extends('layouts.app')

@section('title', 'Fees')
@section('description', 'Track student fee charges, payments and outstanding balances at SV Schools.')

@section('content')
  <section class="page-head">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-2">
          <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Fees</li>
        </ol>
      </nav>
      <div class="d-md-flex align-items-center justify-content-between">
        <div>
          <h1 class="mb-1">Fee Ledger</h1>
          <p class="text-muted mb-0">Record charges, track payments and follow outstanding balances.</p>
        </div>
        <button class="btn btn-sv mt-3 mt-md-0" data-bs-toggle="modal" data-bs-target="#addFeeModal">
          + Add Fee Record
        </button>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ $errors->first() }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="info-tile">
            <div class="stat-value">&#8377; {{ number_format($totalAmount, 2) }}</div>
            <div class="stat-label">Total billed</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-tile">
            <div class="stat-value text-success">&#8377; {{ number_format($collectedAmount, 2) }}</div>
            <div class="stat-label">Collected</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="info-tile">
            <div class="stat-value text-danger">&#8377; {{ number_format($outstandingAmount, 2) }}</div>
            <div class="stat-label">Outstanding</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <label for="feeSearch" class="form-label">Search ledger</label>
          <input type="search" class="form-control" id="feeSearch" placeholder="Student, fee type or status...">
        </div>
        <div class="col-lg-2 d-flex align-items-end">
          <button type="button" class="btn btn-sv-outline w-100" id="resetFeeSearch">Reset</button>
        </div>
      </div>

      <p class="text-muted small">Showing <strong id="feeResultCount">{{ $fees->count() }}</strong> fee record(s).</p>

      <div class="table-card">
        <div class="table-responsive">
          <table class="table align-middle mb-0" id="feeTable">
            <thead>
              <tr>
                <th>Student</th>
                <th>Fee type</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Due date</th>
                <th>Status</th>
                <th class="text-end">Bill</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($fees as $fee)
                @php
                  $statusClass = match ($fee->status) {
                    'Paid' => 'text-bg-success',
                    'Overdue' => 'text-bg-danger',
                    'Partial' => 'text-bg-warning',
                    default => 'text-bg-secondary',
                  };
                @endphp
                <tr class="fee-row">
                  <td data-search="{{ $fee->student->name }}">
                    <span class="avatar me-2">{{ Str::of($fee->student->name)->substr(0, 2)->upper() }}</span>
                    {{ $fee->student->name }}
                  </td>
                  <td data-search="{{ $fee->fee_type }}">{{ $fee->fee_type }}</td>
                  <td>&#8377; {{ number_format($fee->amount, 2) }}</td>
                  <td>&#8377; {{ number_format($fee->paid_amount, 2) }}</td>
                  <td>&#8377; {{ number_format($fee->balance, 2) }}</td>
                  <td>{{ $fee->due_date?->format('d M Y') ?? '-' }}</td>
                  <td data-search="{{ $fee->status }}"><span class="badge {{ $statusClass }}">{{ $fee->status }}</span></td>
                  <td class="text-end">
                    <a href="{{ route('fees.bill', $fee) }}" class="btn btn-sm btn-sv-outline" target="_blank" rel="noopener">
                      Print Bill
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <p class="fw-semibold mb-1">No fee records yet.</p>
                    <p class="text-muted small mb-0">Add a fee record to start tracking collections.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($fees->count())
          <div id="noFeeResults" class="text-center py-5" style="display:none;">
            <p class="fw-semibold mb-1">No fee records match your search.</p>
            <p class="text-muted small mb-0">Try a different keyword or reset the search.</p>
          </div>
        @endif
      </div>
    </div>
  </section>

  <div class="modal fade" id="addFeeModal" tabindex="-1" aria-labelledby="addFeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" action="{{ route('fees.store') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="addFeeLabel">Add Fee Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label for="feeStudent" class="form-label">Student</label>
                <select class="form-select @error('student_id') is-invalid @enderror" id="feeStudent" name="student_id" required>
                  <option value="">Choose a student</option>
                  @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }}</option>
                  @endforeach
                </select>
                @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label for="feeType" class="form-label">Fee type</label>
                <input type="text" class="form-control @error('fee_type') is-invalid @enderror" id="feeType" name="fee_type" value="{{ old('fee_type') }}" placeholder="Tuition" required>
                @error('fee_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label for="feeDueDate" class="form-label">Due date</label>
                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="feeDueDate" name="due_date" value="{{ old('due_date') }}">
                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label for="feeAmount" class="form-label">Total amount</label>
                <input type="number" class="form-control @error('amount') is-invalid @enderror" id="feeAmount" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required>
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label for="feePaidAmount" class="form-label">Paid amount</label>
                <input type="number" class="form-control @error('paid_amount') is-invalid @enderror" id="feePaidAmount" name="paid_amount" value="{{ old('paid_amount', 0) }}" min="0" step="0.01" required>
                @error('paid_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label for="feeNotes" class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="feeNotes" name="notes" rows="2">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sv">Save Fee Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    $(function () {
      var search = $('#feeSearch');
      var rows = $('#feeTable .fee-row');
      var count = $('#feeResultCount');
      var empty = $('#noFeeResults');

      function filterFees() {
        var query = search.val().toLowerCase().trim();
        var visible = 0;

        rows.each(function () {
          var matches = $(this).text().toLowerCase().includes(query);
          $(this).toggle(matches);
          if (matches) visible++;
        });

        count.text(visible);
        empty.toggle(visible === 0 && rows.length > 0);
      }

      search.on('input', filterFees);
      $('#resetFeeSearch').on('click', function () {
        search.val('');
        filterFees();
      });

      @if ($errors->any())
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addFeeModal')).show();
      @endif
    });
  </script>
@endpush
