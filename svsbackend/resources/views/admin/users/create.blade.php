@extends('layouts.app')
@section('title', 'Add User')
@section('content')
<section class="page-head"><div class="container"><h1 class="mb-1">Add user</h1><p class="text-muted mb-0">Create a managed SV Schools account.</p></div></section>
<section class="section"><div class="container"><div class="row justify-content-center"><div class="col-lg-8"><div class="form-card"><form method="POST" action="{{ route('admin.users.store') }}">@include('admin.users._form')<div class="d-flex gap-2 mt-4"><button class="btn btn-sv" type="submit">Create user</button><a class="btn btn-light" href="{{ route('admin.users.index') }}">Cancel</a></div></form></div></div></div></div></section>
@endsection
