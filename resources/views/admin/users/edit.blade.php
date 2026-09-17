@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit User</h1>
        <p class="page-subtitle">{{ $user->name }}</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="mini-avatar" style="width:40px;height:40px;font-size:15px;">
                        @if($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="">
                        @else{{ $user->initials }}@endif
                    </div>
                    <div>
                        <h6 class="mb-0" style="font-size:14px;font-weight:700;">{{ $user->name }}</h6>
                        <small class="text-muted">{{ $user->email }}</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label>
                            <input type="password" name="password" class="form-control" minlength="8">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="client"   {{ old('role', $user->role) === 'client'   ? 'selected' : '' }}>Client</option>
                                <option value="admin"    {{ old('role', $user->role) === 'admin'    ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $user->job_title) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Avatar</label>
                        @if($user->avatar)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$user->avatar) }}" alt="" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
                        </div>
                        @endif
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <label class="form-check-label" for="isActive">Active account</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Save Changes</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
