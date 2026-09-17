@extends('layouts.app')
@section('title', 'Add User')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add User</h1>
        <p class="page-subtitle">Create a new team member account</p>
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
            <div class="card-header"><h6 class="mb-0" style="font-size:14px;font-weight:700;">User Details</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="client"   {{ old('role') === 'client'   ? 'selected' : '' }}>Client</option>
                                <option value="admin"    {{ old('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}" placeholder="e.g. Developer">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Avatar (optional)</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Active account</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Create User</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
