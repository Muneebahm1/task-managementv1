@extends('layouts.app')

@section('title', 'All Tasks')

@section('content')
@php /** @var \App\Models\User $authUser */ $authUser = auth()->user(); @endphp
<div class="page-header">
    <div>
        <h1 class="page-title">All Tasks</h1>
        <p class="page-subtitle">{{ $activeTasks->total() }} active &bull; {{ $completedTasks->total() }} completed</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if(!$authUser->isClient())
        <a href="{{ route('tasks.board') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-kanban"></i> Board
        </a>
        @endif
        @if($authUser->isAdmin())
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Create Task
        </a>
        @elseif($authUser->isClient())
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> Submit Request
        </a>
        @endif
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('tasks.index') }}" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#6B778C;font-size:14px;"></i>
                        <input type="text" name="search" class="form-control" style="padding-left:32px;"
                               placeholder="Search tasks…" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="priority" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Priorities</option>
                        <option value="highest" {{ request('priority') === 'highest' ? 'selected' : '' }}>Highest</option>
                        <option value="high"    {{ request('priority') === 'high'    ? 'selected' : '' }}>High</option>
                        <option value="medium"  {{ request('priority') === 'medium'  ? 'selected' : '' }}>Medium</option>
                        <option value="low"     {{ request('priority') === 'low'     ? 'selected' : '' }}>Low</option>
                        <option value="lowest"  {{ request('priority') === 'lowest'  ? 'selected' : '' }}>Lowest</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Types</option>
                        <option value="task"        {{ request('type') === 'task'        ? 'selected' : '' }}>Task</option>
                        <option value="bug"         {{ request('type') === 'bug'         ? 'selected' : '' }}>Bug</option>
                        <option value="feature"     {{ request('type') === 'feature'     ? 'selected' : '' }}>Feature</option>
                        <option value="story"       {{ request('type') === 'story'       ? 'selected' : '' }}>Story</option>
                        <option value="epic"        {{ request('type') === 'epic'        ? 'selected' : '' }}>Epic</option>
                        <option value="improvement" {{ request('type') === 'improvement' ? 'selected' : '' }}>Improvement</option>
                    </select>
                </div>
                @if(!$authUser->isClient())
                <div class="col-6 col-md-2">
                    <select name="assignee" class="form-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Assignees</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if(request()->anyFilled(['search','status','priority','type','assignee']))
                <div class="col-auto">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Active Tasks Table ─────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
        <span style="font-weight:600;color:#172B4D;">
            <i class="bi bi-list-task me-2 text-primary"></i>Active Tasks
            <span class="badge bg-primary ms-2">{{ $activeTasks->total() }}</span>
            @if($unreadCount > 0)
            <span class="badge bg-danger ms-1" id="unreadCountBadge" title="{{ $unreadCount }} unread">
                {{ $unreadCount }} unread
            </span>
            @endif
        </span>
    </div>
    <div class="card-body p-0">
        @if($activeTasks->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-check2-all" style="font-size:40px;color:#36B37E;"></i>
                <p class="text-muted mt-2">No active tasks. All caught up!</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:110px">Key</th>
                        <th>Title</th>
                        <th style="width:50px"></th>
                        <th style="width:140px">Status</th>
                        <th style="width:120px">Priority</th>
                        <th style="width:160px">Assignee</th>
                        <th style="width:110px">Due Date</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($activeTasks as $task)
                <tr class="task-row" data-href="{{ route('tasks.show', $task) }}"
                    data-task-id="{{ $task->id }}"
                    data-unread="{{ $task->isUnreadFor($authUser->id) ? '1' : '0' }}"
                    style="cursor:pointer">
                    <td><span class="task-key">{{ $task->task_key }}</span></td>
                    <td>
                        <div class="d-flex align-items-start gap-2">
                            <i class="{{ $task->type_icon }}" style="color:{{ $task->type_color }};font-size:15px;flex-shrink:0;margin-top:2px;"></i>
                            <div>
                                <div style="font-size:13.5px;font-weight:500;color:#172B4D;">{{ $task->title }}</div>
                                @if($task->labels)
                                <div class="mt-1">
                                    @foreach(explode(',', $task->labels) as $label)
                                    <span class="badge bg-light text-secondary border" style="font-size:10px;">{{ trim($label) }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($task->isUnreadFor($authUser->id))
                        <span class="badge rounded-pill text-bg-danger" style="font-size:9px;font-weight:700;letter-spacing:.4px;">NEW</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <span class="status-pill" style="background:{{ $task->status->color }}">
                                {{ $task->status->name }}
                            </span>
                            @if($task->isPendingApproval())
                            <span class="badge bg-warning text-dark" style="font-size:9px;">
                                <i class="bi bi-clock-fill"></i>
                            </span>
                            @endif
                        </div>
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
                                @else {{ $task->assignee->initials }} @endif
                            </div>
                            <span style="font-size:12px;">{{ $task->assignee->name }}</span>
                        </div>
                        @else
                            <span class="text-muted" style="font-size:12px;">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if($task->due_date)
                            @if($task->isOverdue())
                                <span class="overdue-badge">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $task->due_date->format('M d') }}
                                </span>
                            @else
                                <span style="font-size:12px;color:#6B778C;">{{ $task->due_date->format('M d, Y') }}</span>
                            @endif
                        @else
                            <span style="color:#DFE1E6;">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($authUser->isEmployee() && !$task->assignee_id)
                            <button type="button" class="btn btn-sm btn-outline-success assign-self-btn"
                                    data-task-id="{{ $task->id }}" title="Assign to me">
                                <i class="bi bi-person-check-fill"></i>
                            </button>
                            @endif
                            @if($authUser->isAdmin())
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                  onsubmit="return confirm('Delete {{ $task->task_key }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
        @if($activeTasks->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <span style="font-size:13px;color:#6B778C;">
                Showing {{ $activeTasks->firstItem() }}–{{ $activeTasks->lastItem() }} of {{ $activeTasks->total() }}
            </span>
            {{ $activeTasks->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- ── Completed Tasks Table ──────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-2 px-3"
         style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#completedSection">
        <span style="font-weight:600;color:#172B4D;">
            <i class="bi bi-check2-circle me-2 text-success"></i>Completed Tasks
            <span class="badge bg-success ms-2">{{ $completedTasks->total() }}</span>
        </span>
        <i class="bi bi-chevron-down text-muted" id="completedChevron"></i>
    </div>
    <div id="completedSection" class="collapse @if($completedTasks->isNotEmpty()) show @endif">
        <div class="card-body p-0">
            @if($completedTasks->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No completed tasks yet.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="opacity:.85;">
                    <thead>
                        <tr>
                            <th style="width:110px">Key</th>
                            <th>Title</th>
                            <th style="width:50px"></th>
                            <th style="width:140px">Status</th>
                            <th style="width:120px">Priority</th>
                            <th style="width:160px">Assignee</th>
                            <th style="width:110px">Completed</th>
                            <th style="width:80px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($completedTasks as $task)
                    <tr onclick="window.location='{{ route('tasks.show', $task) }}'" style="cursor:pointer;">
                        <td><span class="task-key" style="text-decoration:line-through;opacity:.6;">{{ $task->task_key }}</span></td>
                        <td>
                            <div class="d-flex align-items-start gap-2">
                                <i class="{{ $task->type_icon }}" style="color:#6B778C;font-size:15px;flex-shrink:0;margin-top:2px;"></i>
                                <div style="font-size:13.5px;color:#6B778C;text-decoration:line-through;">{{ $task->title }}</div>
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
                            <span class="priority-badge" style="background:#6B778C22;color:#6B778C;">
                                <i class="{{ $task->priority_icon }}"></i>
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td>
                            @if($task->assignee)
                            <div class="d-flex align-items-center gap-2">
                                <div class="mini-avatar" style="opacity:.7;">
                                    @if($task->assignee->avatar)
                                        <img src="{{ asset('storage/' . $task->assignee->avatar) }}" alt="">
                                    @else {{ $task->assignee->initials }} @endif
                                </div>
                                <span style="font-size:12px;color:#6B778C;">{{ $task->assignee->name }}</span>
                            </div>
                            @else
                                <span class="text-muted" style="font-size:12px;">Unassigned</span>
                            @endif
                        </td>
                        <td><span style="font-size:12px;color:#6B778C;">{{ $task->updated_at->format('M d, Y') }}</span></td>
                        <td onclick="event.stopPropagation()">
                            <div class="d-flex gap-1">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($authUser->isAdmin())
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                      onsubmit="return confirm('Delete {{ $task->task_key }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
            @if($completedTasks->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between">
                <span style="font-size:13px;color:#6B778C;">
                    Showing {{ $completedTasks->firstItem() }}–{{ $completedTasks->lastItem() }} of {{ $completedTasks->total() }}
                </span>
                {{ $completedTasks->links() }}
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Collapse chevron rotation — runs after Bootstrap JS is available
document.getElementById('completedSection').addEventListener('hide.bs.collapse', function () {
    document.getElementById('completedChevron').classList.replace('bi-chevron-down','bi-chevron-right');
});
document.getElementById('completedSection').addEventListener('show.bs.collapse', function () {
    document.getElementById('completedChevron').classList.replace('bi-chevron-right','bi-chevron-down');
});

// Row click → mark read then navigate (jQuery available here)
$(document).on('click', '.task-row', function (e) {
    if ($(e.target).closest('button, a, form').length) return;
    const href   = $(this).data('href');
    const taskId = $(this).data('task-id');
    const unread = $(this).data('unread') == '1';
    const $row   = $(this);

    if (unread) {
        $row.find('.badge.text-bg-danger').remove();
        $row.data('unread', '0');
        const $ub = $('#unreadCountBadge');
        if ($ub.length) {
            const cur = parseInt($ub.text()) || 0;
            if (cur <= 1) $ub.remove();
            else $ub.text((cur - 1) + ' unread');
        }
        $.ajax({
            url: '/tasks/' + taskId + '/mark-read', method: 'PATCH',
            complete: function () { window.location = href; }
        });
    } else {
        window.location = href;
    }
});

// Self-assign
$(document).on('click', '.assign-self-btn', function (e) {
    e.stopPropagation();
    const taskId = $(this).data('task-id');
    const $btn   = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: '/tasks/' + taskId + '/assign-self',
        method: 'PATCH',
        success: function (res) {
            const $row = $btn.closest('tr');
            $row.find('td:nth-child(6)').html(
                '<div class="d-flex align-items-center gap-2">' +
                '<div class="mini-avatar" style="background:#0052CC;color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">' + res.assignee_initials + '</div>' +
                '<span style="font-size:12px;">' + res.assignee_name + '</span>' +
                '</div>'
            );
            $btn.remove();
        },
        error: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-person-check-fill"></i>');
        }
    });
});
</script>
@endpush