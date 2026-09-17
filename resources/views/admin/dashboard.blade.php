@extends('layouts.app')

@section('title', 'Admin Panel')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Admin Panel</h1>
        <p class="page-subtitle">System overview and management</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DEEBFF;color:#0052CC"><i class="bi bi-list-task"></i></div>
            <div class="stat-info">
                <div class="stat-value text-primary">{{ $totalTasks }}</div>
                <div class="stat-label">Total Tasks</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#E3FCEF;color:#006644"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#006644">{{ $closedTasks }}</div>
                <div class="stat-label">Closed Tasks</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#FFEBE6;color:#BF2600"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#BF2600">{{ $overdueTasks }}</div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#EAE6FF;color:#6554C0"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#6554C0">{{ $totalUsers }}</div>
                <div class="stat-label">Team Members</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Tasks by Status -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0" style="font-size:14px;font-weight:700;">Tasks by Status</h6>
                <a href="{{ route('admin.statuses.index') }}" class="text-primary" style="font-size:12px;">Manage →</a>
            </div>
            <div class="card-body">
                @foreach($statuses as $s)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $s->color }};display:inline-block;"></span>
                        <span style="font-size:13px;">{{ $s->name }}</span>
                    </div>
                    <span class="badge" style="background:{{ $s->color }}22;color:{{ $s->color }};font-size:12px;font-weight:700;">{{ $s->tasks_count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tasks by Type -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0" style="font-size:14px;font-weight:700;">Tasks by Type</h6>
            </div>
            <div class="card-body">
                @php
                    $typeConfig = [
                        'task'=>['Task','bi-check2-square','#0052CC'],
                        'bug'=>['Bug','bi-bug-fill','#FF5630'],
                        'feature'=>['Feature','bi-stars','#36B37E'],
                        'story'=>['Story','bi-book-fill','#00B8D9'],
                        'epic'=>['Epic','bi-lightning-fill','#6554C0'],
                        'improvement'=>['Improvement','bi-arrow-up-right-circle-fill','#FF8C00'],
                    ];
                @endphp
                @foreach($typeConfig as $key => [$label, $icon, $color])
                @php $count = $tasksByType[$key] ?? 0; @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="{{ $icon }}" style="color:{{ $color }};font-size:18px;width:22px;"></i>
                    <div class="flex-1 w-100">
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:13px;">{{ $label }}</span>
                            <span style="font-size:12px;font-weight:700;">{{ $count }}</span>
                        </div>
                        <div class="progress" style="height:4px;border-radius:4px;">
                            <div class="progress-bar" style="width:{{ $totalTasks > 0 ? round($count/$totalTasks*100) : 0 }}%;background:{{ $color }};"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Assignees -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0" style="font-size:14px;font-weight:700;">Top Assignees</h6>
                <a href="{{ route('admin.users.index') }}" class="text-primary" style="font-size:12px;">All users →</a>
            </div>
            <div class="card-body">
                @forelse($topAssignees as $u)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="mini-avatar" style="width:32px;height:32px;font-size:12px;">
                        @if($u->avatar)<img src="{{ asset('storage/'.$u->avatar) }}" alt="">
                        @else{{ $u->initials }}@endif
                    </div>
                    <div class="flex-1">
                        <div style="font-size:13px;font-weight:600;">{{ $u->name }}</div>
                        <div style="font-size:11px;color:#6B778C;">{{ $u->assigned_tasks_count }} tasks assigned</div>
                    </div>
                    <span class="badge bg-primary" style="font-size:11px;">{{ $u->assigned_tasks_count }}</span>
                </div>
                @empty
                <p class="text-muted" style="font-size:13px;">No assignees yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0" style="font-size:14px;font-weight:700;"><i class="bi bi-activity me-2 text-primary"></i>Recent Activity</h6>
    </div>
    <div class="card-body p-3">
        @forelse($recentActivity as $act)
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="mini-avatar" style="width:30px;height:30px;font-size:11px;flex-shrink:0;">
                @if($act->user->avatar)<img src="{{ asset('storage/'.$act->user->avatar) }}" alt="">
                @else{{ $act->user->initials }}@endif
            </div>
            <div>
                <span style="font-size:13px;font-weight:600;">{{ $act->user->name }}</span>
                <span style="font-size:13px;color:#6B778C;"> {!! $act->description !!} on </span>
                <a href="{{ route('tasks.show', $act->task) }}" style="font-size:13px;font-weight:600;">{{ $act->task->task_key }}</a>
                <span style="font-size:11px;color:#9BA8B4;margin-left:6px;">{{ $act->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-3" style="font-size:13px;">No activity yet.</p>
        @endforelse
    </div>
</div>
@endsection
