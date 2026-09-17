<div class="board-card fade-in" data-task-id="{{ $task->id }}">
    <div class="d-flex align-items-center justify-content-between mb-1">
        <span class="card-task-key">{{ $task->task_key }}</span>
        <i class="{{ $task->type_icon }}" style="color:{{ $task->type_color }};font-size:13px;"></i>
    </div>
    <a href="{{ route('tasks.show', $task) }}" class="card-title text-decoration-none d-block" style="color:#172B4D;">
        {{ $task->title }}
    </a>
    <div class="card-meta">
        <div class="d-flex align-items-center gap-1">
            <i class="{{ $task->priority_icon }}" style="color:{{ $task->priority_color }};font-size:14px;" title="{{ ucfirst($task->priority) }} priority"></i>
            @if($task->due_date)
                @if($task->isOverdue())
                    <span class="overdue-badge ms-1"><i class="bi bi-clock"></i> {{ $task->due_date->format('M d') }}</span>
                @else
                    <span style="font-size:11px;color:#6B778C;margin-left:4px;"><i class="bi bi-calendar3"></i> {{ $task->due_date->format('M d') }}</span>
                @endif
            @endif
        </div>
        @if($task->assignee)
        <div class="mini-avatar" title="{{ $task->assignee->name }}">
            @if($task->assignee->avatar)
                <img src="{{ asset('storage/' . $task->assignee->avatar) }}" alt="">
            @else
                {{ $task->assignee->initials }}
            @endif
        </div>
        @else
        <div class="mini-avatar" style="background:#DFE1E6;color:#6B778C;" title="Unassigned">
            <i class="bi bi-person" style="font-size:12px;"></i>
        </div>
        @endif
    </div>
</div>
