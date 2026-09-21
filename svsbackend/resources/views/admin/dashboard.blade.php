@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('description', 'SV Schools administration dashboard.')

@section('content')
<section class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-2"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item active">Administration</li></ol></nav>
    <h1 class="mb-1">Admin Dashboard</h1>
    <p class="text-muted mb-0">Manage SV Schools users and operations.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="row g-4 mb-4">
      @foreach ([['Total users', $totalUsers], ['Students', $totalStudents], ['Teachers', $totalTeachers], ['Staff', $totalStaff], ['Parents', $totalParents], ['Classes', $totalClasses], ['Subjects', $totalSubjects], ['Active users', $activeUsers]] as [$label, $value])
        <div class="col-md-6 col-lg-4 col-xl-2"><div class="info-tile h-100"><div class="stat-value">{{ number_format($value) }}</div><div class="stat-label">{{ $label }}</div></div></div>
      @endforeach
    </div>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="table-card">
          <div class="p-4 d-flex justify-content-between align-items-center"><h5 class="fw-bold mb-0">Recent registrations</h5><a class="btn btn-sv btn-sm" href="{{ route('admin.users.index') }}">Manage users</a></div>
          <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody>
            @forelse ($recentUsers as $user)
              <tr><td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a><div class="small text-muted">{{ $user->email }}</div></td><td>{{ str_replace('_', ' ', ucfirst($user->role)) }}</td><td><span class="badge {{ $user->isActive() ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($user->status) }}</span></td><td>{{ $user->created_at?->format('d M Y') }}</td></tr>
            @empty
              <tr><td colspan="4" class="text-center py-4">No registrations yet.</td></tr>
            @endforelse
          </tbody></table></div>
        </div>
      </div>
      <div class="col-lg-4"><div class="form-card h-100"><h5 class="fw-bold mb-3">Quick actions</h5><div class="d-grid gap-2"><a class="btn btn-sv" href="{{ route('admin.users.create') }}">Add user</a><a class="btn btn-sv-outline" href="{{ route('admin.users.index') }}">Search users</a><a class="btn btn-sv-outline" href="{{ route('dashboard') }}">Main dashboard</a></div></div></div>
    </div>
    <div class="table-card mt-4"><div class="p-4"><h5 class="fw-bold mb-0">Recent activity</h5></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>User</th><th>Module</th><th>Action</th><th>Date</th></tr></thead><tbody>@forelse($recentActivities as $activity)<tr><td>{{ $activity->user?->name ?: 'System' }}</td><td>{{ ucfirst($activity->module) }}</td><td>{{ $activity->description }}</td><td>{{ $activity->created_at->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="4" class="text-center py-3">No management activity yet.</td></tr>@endforelse</tbody></table></div></div>
  </div>
</section>
@endsection
