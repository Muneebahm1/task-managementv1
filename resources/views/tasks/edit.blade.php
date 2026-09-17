@extends('layouts.app')

@section('title', 'Edit ' . $task->task_key)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit {{ $task->task_key }}</h1>
        <p class="page-subtitle">{{ $task->title }}</p>
    </div>
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-circle-fill me-2"></i>
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('tasks.update', $task) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;">Task Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg"
                               value="{{ old('title', $task->title) }}" required
                               style="font-size:16px;font-weight:600;">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="8">{{ old('description', $task->description) }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                @foreach(['task','bug','feature','story','epic','improvement'] as $t)
                                <option value="{{ $t }}" {{ old('type', $task->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                @foreach(['highest','high','medium','low','lowest'] as $p)
                                <option value="{{ $p }}" {{ old('priority', $task->priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload more attachments -->
            <div class="card">
                <div class="card-header"><h6 class="mb-0" style="font-size:14px;font-weight:700;"><i class="bi bi-paperclip me-2"></i>Add More Attachments</h6></div>
                <div class="card-body">
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    @if($task->attachments->count())
                    <div class="mt-3">
                        <p class="text-muted mb-2" style="font-size:12px;">Existing attachments ({{ $task->attachments->count() }})</p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($task->attachments as $att)
                            <div class="attachment-thumb">
                                @if($att->isImage())<img src="{{ $att->url }}" alt="">
                                @else<i class="bi {{ $att->icon }}"></i><small style="font-size:10px;margin-top:4px;">{{ \Str::limit($att->original_name,12) }}</small>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0" style="font-size:14px;font-weight:700;">Planning</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status_id" class="form-select" required>
                            @foreach($statuses as $s)
                            <option value="{{ $s->id }}" {{ old('status_id', $task->status_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignee</label>
                        <select name="assignee_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('assignee_id', $task->assignee_id) == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ ucfirst($u->role) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Story Points</label>
                        <input type="number" name="story_points" class="form-control" min="0" value="{{ old('story_points', $task->story_points) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-control" min="0" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Labels</label>
                        <input type="text" name="labels" class="form-control" value="{{ old('labels', $task->labels) }}" placeholder="comma-separated">
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Save Changes</button>
                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
