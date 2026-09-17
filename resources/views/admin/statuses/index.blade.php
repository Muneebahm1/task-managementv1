@extends('layouts.app')
@section('title', 'Manage Statuses')

@push('styles')
<style>
    .status-row { cursor: grab; }
    .status-row.sortable-ghost { opacity: .4; background: #DEEBFF; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Task Statuses</h1>
        <p class="page-subtitle">Customize workflow statuses • Drag to reorder</p>
    </div>
    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addStatusModal">
        <i class="bi bi-plus-lg"></i> Add Status
    </button>
</div>

<!-- Status List -->
<div class="card">
    <div class="card-body p-0">
        @if($statuses->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-tags" style="font-size:48px;color:#DFE1E6;"></i>
            <p class="text-muted mt-3">No statuses yet. Add your first status!</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:40px"></th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Color</th>
                        <th>Tasks</th>
                        <th>Flags</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody id="statusList">
                @foreach($statuses as $status)
                <tr class="status-row" data-id="{{ $status->id }}">
                    <td><i class="bi bi-grip-vertical text-muted" style="cursor:grab;"></i></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:14px;height:14px;border-radius:3px;background:{{ $status->color }};display:inline-block;flex-shrink:0;"></span>
                            <span style="font-size:14px;font-weight:600;">{{ $status->name }}</span>
                            @if($status->is_default)
                                <span class="badge bg-primary" style="font-size:10px;">Default</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @php $catColors = ['backlog'=>['#6B778C','#F4F5F7'],'active'=>['#0052CC','#DEEBFF'],'done'=>['#006644','#E3FCEF']]; @endphp
                        <span class="badge" style="background:{{ $catColors[$status->category][1] ?? '#f0f0f0' }};color:{{ $catColors[$status->category][0] ?? '#333' }};font-size:11px;">
                            {{ ucfirst($status->category) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:24px;height:24px;border-radius:4px;background:{{ $status->color }};display:inline-block;"></span>
                            <code style="font-size:12px;">{{ $status->color }}</code>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:13px;">{{ $status->tasks_count }}</span>
                    </td>
                    <td>
                        @if($status->is_closed)
                        <span class="badge text-bg-success" style="font-size:10px;">Closes Task</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick="openEdit({{ $status->id }}, '{{ $status->name }}', '{{ $status->color }}', '{{ $status->category }}', {{ $status->is_default ? 1 : 0 }}, {{ $status->is_closed ? 1 : 0 }})"
                                    title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if($status->tasks_count == 0)
                            <form method="POST" action="{{ route('admin.statuses.destroy', $status) }}"
                                  onsubmit="return confirm('Delete status {{ $status->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                            <button class="btn btn-sm btn-outline-secondary" disabled title="Has tasks — can't delete">
                                <i class="bi bi-lock"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- ADD Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700">Add New Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.statuses.store') }}" id="addStatusForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. In Review" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" name="color" id="addColor" class="form-control form-control-color" value="#0052CC">
                                <input type="text" id="addColorText" class="form-control" value="#0052CC" placeholder="#HEX" maxlength="7"
                                       oninput="document.getElementById('addColor').value=this.value">
                            </div>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @php $presets = ['#0052CC','#36B37E','#FF5630','#FF8C00','#6554C0','#00B8D9','#FFC400','#6B778C','#172B4D']; @endphp
                                @foreach($presets as $p)
                                <span onclick="document.getElementById('addColor').value='{{ $p }}';document.getElementById('addColorText').value='{{ $p }}';"
                                      style="width:22px;height:22px;border-radius:4px;background:{{ $p }};display:inline-block;cursor:pointer;border:2px solid transparent;"
                                      class="color-preset"></span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="backlog">Backlog</option>
                                <option value="active" selected>Active</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="addDefault">
                                <label class="form-check-label" for="addDefault">Default status for new tasks</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_closed" value="1" id="addClosed">
                                <label class="form-check-label" for="addClosed">Marks task as closed/done</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700">Edit Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStatusForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Color</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="color" name="color" id="editColor" class="form-control form-control-color">
                                <input type="text" id="editColorText" class="form-control" maxlength="7"
                                       oninput="document.getElementById('editColor').value=this.value">
                            </div>
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach($presets as $p)
                                <span onclick="document.getElementById('editColor').value='{{ $p }}';document.getElementById('editColorText').value='{{ $p }}';"
                                      style="width:22px;height:22px;border-radius:4px;background:{{ $p }};display:inline-block;cursor:pointer;"></span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Category</label>
                            <select name="category" id="editCategory" class="form-select">
                                <option value="backlog">Backlog</option>
                                <option value="active">Active</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editDefault">
                                <label class="form-check-label" for="editDefault">Default status</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_closed" value="1" id="editClosed">
                                <label class="form-check-label" for="editClosed">Marks as closed</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sync color picker with text
    document.getElementById('addColor').addEventListener('input', function() {
        document.getElementById('addColorText').value = this.value;
    });
    document.getElementById('editColor').addEventListener('input', function() {
        document.getElementById('editColorText').value = this.value;
    });

    // Open edit modal
    function openEdit(id, name, color, category, isDefault, isClosed) {
        document.getElementById('editStatusForm').action = '/admin/statuses/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editColor').value = color;
        document.getElementById('editColorText').value = color;
        document.getElementById('editCategory').value = category;
        document.getElementById('editDefault').checked = !!isDefault;
        document.getElementById('editClosed').checked  = !!isClosed;
        new bootstrap.Modal(document.getElementById('editStatusModal')).show();
    }

    // Drag-to-reorder with SortableJS
    new Sortable(document.getElementById('statusList'), {
        animation: 150,
        handle: '.bi-grip-vertical',
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            const order = Array.from(document.querySelectorAll('#statusList tr[data-id]')).map(r => r.dataset.id);
            $.post('/admin/statuses/reorder', { order: order }, function() {});
        }
    });
</script>
@endpush
