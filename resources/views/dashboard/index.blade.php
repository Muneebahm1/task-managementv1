@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php /** @var \App\Models\User $authUser */ $authUser = auth()->user(); @endphp
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}! Here's what's happening.</p>
    </div>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('tasks.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg"></i> New Task
    </a>
    @endif
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DEEBFF; color:#0052CC">
                <i class="bi bi-list-task"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value text-primary">{{ $totalTasks }}</div>
                <div class="stat-label">{{ auth()->user()->isAdmin() ? 'Total Tasks' : 'My Tasks' }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#E3FCEF; color:#006644">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color:#006644">{{ $completedTasks }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#FFEBE6; color:#BF2600">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color:#BF2600">{{ $overdueTasks }}</div>
                <div class="stat-label">Overdue / Urgent</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        @if(auth()->user()->isAdmin())
        <div class="stat-card">
            <div class="stat-icon" style="background:#EAE6FF; color:#6554C0">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color:#6554C0">{{ $totalUsers }}</div>
                <div class="stat-label">Team Members</div>
            </div>
        </div>
        @else
        <div class="stat-card">
            <div class="stat-icon" style="background:#FFFAE6; color:#974F0C">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color:#974F0C">{{ $myTasks }}</div>
                <div class="stat-label">Assigned to Me</div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Status Distribution -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-700" style="font-size:14px;font-weight:700;">Tasks by Status</h6>
                <a href="{{ route('tasks.board') }}" class="text-primary" style="font-size:12px;">View Board →</a>
            </div>
            <div class="card-body p-3">
                @foreach($tasksByStatus as $s)
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $s['color'] }};display:inline-block;flex-shrink:0;"></span>
                            <span style="font-size:13px;font-weight:500;">{{ $s['name'] }}</span>
                        </div>
                        <span style="font-size:13px;font-weight:700;color:#172B4D;">{{ $s['count'] }}</span>
                    </div>
                    @php $pct = $totalTasks > 0 ? round(($s['count'] / $totalTasks) * 100) : 0; @endphp
                    <div class="progress" style="height:5px;border-radius:4px;">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ $pct }}%;background:{{ $s['color'] }};" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                @endforeach
                @if($tasksByStatus->isEmpty())
                    <p class="text-muted text-center py-3" style="font-size:13px;">No statuses configured</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Priority Chart -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0" style="font-size:14px;font-weight:700;">Tasks by Priority</h6>
            </div>
            <div class="card-body p-3">
                @php
                    $priorityConfig = [
                        'highest' => ['label' => 'Highest', 'color' => '#FF0000', 'icon' => 'bi-arrow-up-circle-fill'],
                        'high'    => ['label' => 'High',    'color' => '#FF8C00', 'icon' => 'bi-arrow-up-circle'],
                        'medium'  => ['label' => 'Medium',  'color' => '#FFA500', 'icon' => 'bi-dash-circle'],
                        'low'     => ['label' => 'Low',     'color' => '#2684FF', 'icon' => 'bi-arrow-down-circle'],
                        'lowest'  => ['label' => 'Lowest',  'color' => '#00B8D9', 'icon' => 'bi-arrow-down-circle-fill'],
                    ];
                @endphp
                @foreach($priorityConfig as $key => $cfg)
                @php $count = $tasksByPriority[$key] ?? 0; @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="{{ $cfg['icon'] }}" style="color:{{ $cfg['color'] }};font-size:18px;flex-shrink:0;"></i>
                    <div class="flex-1 w-100">
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:13px;font-weight:500;">{{ $cfg['label'] }}</span>
                            <span style="font-size:13px;font-weight:700;">{{ $count }}</span>
                        </div>
                        @php $pct = $totalTasks > 0 ? round(($count / $totalTasks) * 100) : 0; @endphp
                        <div class="progress" style="height:5px;border-radius:4px;">
                            <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $cfg['color'] }};"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0" style="font-size:14px;font-weight:700;">Quick Actions</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-2">
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('tasks.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Create New Task
                    </a>
                    @endif
                    <a href="{{ route('tasks.board') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                        <i class="bi bi-kanban"></i> Open Board View
                    </a>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-list-task"></i> Browse All Tasks
                    </a>
                    <a href="{{ route('tasks.index') }}?priority=high" class="btn btn-outline-danger d-flex align-items-center gap-2">
                        <i class="bi bi-fire"></i> High Priority Tasks
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.statuses.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-tags"></i> Manage Statuses
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tasks -->
<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0" style="font-size:14px;font-weight:700;">
            <i class="bi bi-clock-history me-2 text-primary"></i>Recent Tasks
        </h6>
        <a href="{{ route('tasks.index') }}" class="text-primary" style="font-size:12px;">View all →</a>
    </div>
    <div class="card-body p-0">
        @if($recentTasks->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size:40px;color:#DFE1E6;"></i>
                <p class="text-muted mt-3" style="font-size:14px;">No tasks yet.
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('tasks.create') }}">Create your first task</a>
                    @endif
                </p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Title</th>
                        <th style="width:50px"></th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assignee</th>
                        <th>Due</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recentTasks as $task)
                <tr onclick="window.location='{{ route('tasks.show', $task) }}'" style="cursor:pointer">
                    <td><span class="task-key">{{ $task->task_key }}</span></td>
                    <td>
                        <div style="font-size:13.5px;font-weight:500;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <i class="{{ $task->type_icon }}" style="color:{{ $task->type_color }};margin-right:6px;"></i>
                            {{ $task->title }}
                        </div>
                    </td>
                    <td>
                        @if($task->isUnreadFor($authUser->id))
                        <span class="badge rounded-pill text-bg-danger" style="font-size:9px;font-weight:700;letter-spacing:.4px;">NEW</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-pill" style="background:{{ $task->status->color }}">
                            {{ $task->status->name }}
                        </span>
                    </td>
                    <td>
                        <span class="priority-badge" style="background:{{ $task->priority_color }}22;color:{{ $task->priority_color }}">
                            <i class="{{ $task->priority_icon }}"></i>
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td>
                        @if($task->assignee)
                        <div class="d-flex align-items-center gap-2">
                            <div class="mini-avatar">
                                @if($task->assignee->avatar)
                                    <img src="{{ asset('storage/' . $task->assignee->avatar) }}" alt="">
                                @else
                                    {{ $task->assignee->initials }}
                                @endif
                            </div>
                            <span style="font-size:12px;">{{ $task->assignee->name }}</span>
                        </div>
                        @else
                            <span class="text-muted" style="font-size:12px;">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if($task->due_date)
                            <span class="{{ $task->isOverdue() ? 'overdue-badge' : '' }}" style="{{ !$task->isOverdue() ? 'font-size:12px;color:#6B778C;' : '' }}">
                                @if($task->isOverdue())<i class="bi bi-exclamation-triangle-fill me-1"></i>@endif
                                {{ $task->due_date->format('M d, Y') }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
