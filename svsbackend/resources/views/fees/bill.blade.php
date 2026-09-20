<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fee Bill #{{ $fee->id }} | SV Schools</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f8; color: #1f2933; }
    .bill { max-width: 820px; margin: 2rem auto; background: #fff; padding: 3rem; }
    .bill-header { border-bottom: 2px solid #0d6e6e; padding-bottom: 1.5rem; }
    .bill-title { color: #0d6e6e; letter-spacing: .08em; }
    .bill-total { background: #eef8f7; border-left: 4px solid #0d6e6e; }
    @media print {
      body { background: #fff; }
      .bill { margin: 0; max-width: none; padding: 1rem; }
      .print-actions { display: none !important; }
    }
  </style>
</head>
<body>
  <main class="bill">
    <div class="print-actions d-flex justify-content-end gap-2 mb-4">
      <a href="{{ route('fees') }}" class="btn btn-outline-secondary">Back to Fees</a>
      <button type="button" class="btn btn-success" onclick="window.print()">Print Bill</button>
    </div>

    <header class="bill-header d-flex justify-content-between align-items-start gap-3">
      <div>
        <h1 class="h3 mb-1">SV Schools</h1>
        <p class="text-muted mb-0">Station Road, Surat, Gujarat 395003</p>
        <p class="text-muted mb-0">office@svschools.edu | +91 98765 43210</p>
      </div>
      <div class="text-end">
        <p class="bill-title fw-bold mb-1">FEE BILL</p>
        <p class="mb-0">Bill #{{ str_pad($fee->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p class="text-muted mb-0">{{ $fee->created_at->format('d M Y') }}</p>
      </div>
    </header>

    <section class="row g-4 py-4">
      <div class="col-sm-6">
        <p class="text-muted small text-uppercase mb-1">Billed to</p>
        <h2 class="h5 mb-1">{{ $fee->student->name }}</h2>
        <p class="text-muted mb-0">{{ $fee->student->email }}</p>
      </div>
      <div class="col-sm-6 text-sm-end">
        <p class="text-muted small text-uppercase mb-1">Payment status</p>
        <span class="badge text-bg-{{ $fee->status === 'Paid' ? 'success' : ($fee->status === 'Overdue' ? 'danger' : 'warning') }}">
          {{ $fee->status }}
        </span>
      </div>
    </section>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Description</th>
            <th>Due date</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $fee->fee_type }}</td>
            <td>{{ $fee->due_date?->format('d M Y') ?? '-' }}</td>
            <td class="text-end">&#8377; {{ number_format($fee->amount, 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <section class="bill-total p-3 mt-4 ms-auto" style="max-width: 360px;">
      <div class="d-flex justify-content-between mb-2">
        <span>Total billed</span>
        <strong>&#8377; {{ number_format($fee->amount, 2) }}</strong>
      </div>
      <div class="d-flex justify-content-between mb-2 text-success">
        <span>Amount paid</span>
        <strong>&#8377; {{ number_format($fee->paid_amount, 2) }}</strong>
      </div>
      <div class="d-flex justify-content-between border-top pt-2">
        <span>Balance due</span>
        <strong>&#8377; {{ number_format($fee->balance, 2) }}</strong>
      </div>
    </section>

    @if ($fee->notes)
      <p class="text-muted small mt-4 mb-0"><strong>Notes:</strong> {{ $fee->notes }}</p>
    @endif
    <p class="text-muted small text-center mt-5 mb-0">Thank you for your payment.</p>
  </main>
</body>
</html>