@extends('layouts.app')

@section('title', 'Board')

@push('styles')
<style>
    #main-content { padding: 20px 24px; }
</style>
@endpush

@section('content')
<div class="page-header mb-3">
    <div>
        <h1 class="page-title">Board</h1>
        <p class="page-subtitle">Drag cards to change status</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-task me-1"></i>List View
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Create Task
        </a>
        @endif
    </div>
</div>

<!-- Toast notification -->
<div class="position-fixed" style="bottom:24px;right:24px;z-index:9999;">
    <div id="boardToast" class="toast align-items-center text-bg-success border-0" role="alert" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body fw-500" id="boardToastMsg">Status updated!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<div class="board-wrapper" id="board">
    @foreach($statuses as $status)
    {{-- Hide cancelled column for employees, show only for admins --}}
    @if($status->slug === 'cancelled' && !auth()->user()->isAdmin())
        @continue
    @endif
    <div class="board-col" data-status-id="{{ $status->id }}">
        <div class="board-col-header">
            <span class="col-dot" style="background:{{ $status->color }}"></span>
            <span class="col-name">{{ $status->name }}</span>
            <span class="col-count" id="count-{{ $status->id }}">{{ $tasksByStatus[$status->id]->count() }}</span>
        </div>
        <div class="board-cards" id="col-{{ $status->id }}" data-status="{{ $status->id }}">
            @foreach($tasksByStatus[$status->id] as $task)
            @include('tasks._board_card', ['task' => $task])
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    const updateStatusUrl = '/tasks/:id/status';
    const toast = new bootstrap.Toast(document.getElementById('boardToast'));

    // Init Sortable on each column
    document.querySelectorAll('.board-cards').forEach(function(el) {
        new Sortable(el, {
            group: 'board',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                const taskId   = evt.item.dataset.taskId;
                const statusId = evt.to.dataset.status;

                // Update counts
                document.querySelectorAll('.board-cards').forEach(function(col) {
                    const id = col.dataset.status;
                    document.getElementById('count-' + id).textContent = col.querySelectorAll('.board-card').length;
                });

                $.ajax({
                    url: updateStatusUrl.replace(':id', taskId),
                    method: 'PATCH',
                    data: { status_id: statusId },
                    success: function(res) {
                        document.getElementById('boardToastMsg').textContent = 'Status updated!';
                        document.getElementById('boardToast').classList.remove('text-bg-danger');
                        document.getElementById('boardToast').classList.add('text-bg-success');
                        toast.show();
                    },
                    error: function(xhr) {
                        // Revert the move if it failed
                        evt.from.appendChild(evt.item);
                        
                        // Update counts again after revert
                        document.querySelectorAll('.board-cards').forEach(function(col) {
                            const id = col.dataset.status;
                            document.getElementById('count-' + id).textContent = col.querySelectorAll('.board-card').length;
                        });
                        
                        const errorMsg = xhr.responseJSON?.error || 'Update failed!';
                        document.getElementById('boardToastMsg').textContent = errorMsg;
                        document.getElementById('boardToast').classList.remove('text-bg-success');
                        document.getElementById('boardToast').classList.add('text-bg-danger');
                        toast.show();
                    }
                });
            }
        });
    });
</script>
@endpush
