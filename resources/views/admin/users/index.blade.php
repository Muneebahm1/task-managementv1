@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">{{ $users->total() }} registered users</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-person-plus"></i> Add User
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($users->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-people" style="font-size:48px;color:#DFE1E6;"></i>
            <p class="text-muted mt-3">No users found.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Job Title</th>
                        <th>Assigned</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="mini-avatar" style="width:36px;height:36px;font-size:13px;">
                                @if($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="">
                                @else{{ $user->initials }}@endif
                            </div>
                            <div>
                                <div style="font-size:13.5px;font-weight:600;color:#172B4D;">{{ $user->name }}</div>
                                <div style="font-size:12px;color:#6B778C;">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $roleClass = match($user->role) {
                                'admin'    => 'text-bg-primary',
                                'client'   => 'text-bg-purple',
                                default    => 'text-bg-secondary',
                            };
                        @endphp
                        <span class="badge" style="font-size:11px;background:{{ $user->role_badge_color }};color:#fff;">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td style="font-size:13px;color:#6B778C;">{{ $user->job_title ?? '—' }}</td>
                    <td>
                        <span style="font-size:13px;">{{ $user->assigned_tasks_count }} tasks</span>
                    </td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   {{ $user->is_active ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                   onchange="toggleActive({{ $user->id }}, this)"
                                   title="{{ $user->is_active ? 'Active' : 'Inactive' }}">
                        </div>
                    </td>
                    <td style="font-size:12px;color:#6B778C;">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Delete {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer">{{ $users->links() }}</div>
        @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleActive(userId, el) {
        $.ajax({
            url: '/admin/users/' + userId + '/toggle-active',
            method: 'PATCH',
            success: function(res) {
                el.checked = res.is_active;
            },
            error: function(xhr) {
                el.checked = !el.checked;
                alert(xhr.responseJSON?.error || 'Error');
            }
        });
    }
</script>
@endpush
