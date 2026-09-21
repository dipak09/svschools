@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<section class="page-head"><div class="container"><h1 class="mb-1">Edit user</h1><p class="text-muted mb-0">Update {{ $user->name }}'s account.</p></div></section>
<section class="section"><div class="container"><div class="row justify-content-center"><div class="col-lg-8"><div class="form-card"><form method="POST" action="{{ route('admin.users.update', $user) }}">@method('PUT')@include('admin.users._form')<div class="d-flex gap-2 mt-4"><button class="btn btn-sv" type="submit">Save changes</button><a class="btn btn-light" href="{{ route('admin.users.index') }}">Cancel</a></div></form></div></div></div></div></section>
@endsection
