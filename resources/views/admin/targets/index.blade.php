@extends('layouts.app')

@section('title', 'Targets')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Targets</h1>
        <p class="page-subtitle">Track your closing targets and client acquisition goals</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addTargetModal">
        <i class="bi bi-plus-lg"></i> New Target
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($targets->isEmpty())
<div class="card">
    <div class="text-center py-5">
        <i class="bi bi-bullseye" style="font-size:56px;color:#DFE1E6;"></i>
        <p class="text-muted mt-3">No targets yet. Create your first one!</p>
        <button class="btn btn-primary btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#addTargetModal">
            <i class="bi bi-plus-lg me-1"></i> Create Target
        </button>
    </div>
</div>
@else
<div class="row g-4">
@foreach($targets as $target)
@php
    $closed  = $target->clients_count ?? $target->closed_count;
    $pct     = $target->total_target > 0 ? min(100, round(($closed / $target->total_target) * 100)) : 0;
    $endDate = $target->start_date->addMonths($target->timeline_months);
    $daysLeft = max(0, now()->startOfDay()->diffInDays($endDate, false));
    $isExpired = now()->gt($endDate);
    $remaining = max(0, $target->total_target - $closed);
    $progressColor = $pct >= 100 ? '#36B37E' : ($pct >= 60 ? '#0052CC' : ($isExpired ? '#BF2600' : '#FF8C00'));
@endphp
<div class="col-md-6 col-xl-4">
    <div class="card h-100" style="border-left: 4px solid {{ $progressColor }};">
        <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="flex-1">
                    <h6 style="font-weight:700;font-size:15px;color:#172B4D;margin-bottom:4px;">{{ $target->title }}</h6>
                    @if($target->description)
                    <p style="font-size:12px;color:#6B778C;margin-bottom:0;">{{ Str::limit($target->description, 80) }}</p>
                    @endif
                </div>
                <div class="d-flex gap-1 ms-2 flex-shrink-0">
                    @if(!$target->is_active)
                    <span class="badge bg-secondary">Inactive</span>
                    @elseif($isExpired)
                    <span class="badge bg-danger">Expired</span>
                    @else
                    <span class="badge bg-success">Active</span>
                    @endif
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:12px;color:#6B778C;">Progress</span>
                    <span style="font-size:12px;font-weight:700;color:{{ $progressColor }};">{{ $pct }}%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:6px;background:#DFE1E6;">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $progressColor }};border-radius:6px;transition:width .4s ease;"></div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="row g-2 mb-3">
                <div class="col-4 text-center p-2 rounded-3" style="background:#DEEBFF;">
                    <div style="font-size:20px;font-weight:800;color:#0052CC;">{{ $target->total_target }}</div>
                    <div style="font-size:10px;color:#0052CC;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Target</div>
                </div>
                <div class="col-4 text-center p-2 rounded-3" style="background:#E3FCEF;">
                    <div style="font-size:20px;font-weight:800;color:#36B37E;">{{ $closed }}</div>
                    <div style="font-size:10px;color:#36B37E;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Closed</div>
                </div>
                <div class="col-4 text-center p-2 rounded-3" style="background:{{ $isExpired ? '#FFEBE6' : '#FFFAE6' }};">
                    <div style="font-size:20px;font-weight:800;color:{{ $isExpired ? '#BF2600' : '#974F0C' }};">{{ $remaining }}</div>
                    <div style="font-size:10px;color:{{ $isExpired ? '#BF2600' : '#974F0C' }};font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Remaining</div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="d-flex align-items-center justify-content-between" style="font-size:12px;color:#6B778C;">
                <span><i class="bi bi-calendar3 me-1"></i>{{ $target->start_date->format('M d, Y') }} → {{ $endDate->format('M d, Y') }}</span>
                @if($isExpired)
                <span class="text-danger fw-600"><i class="bi bi-exclamation-circle me-1"></i>Expired</span>
                @else
                <span><i class="bi bi-clock me-1"></i>{{ $daysLeft }} days left</span>
                @endif
            </div>
        </div>
        <div class="card-footer d-flex gap-2 py-2 px-4" style="background:#F8F9FA;">
            <a href="{{ route('admin.targets.show', $target) }}" class="btn btn-sm btn-primary flex-1">
                <i class="bi bi-eye me-1"></i>View & Manage
            </a>
            <button class="btn btn-sm btn-outline-secondary"
                    onclick="openEditTarget({{ $target->id }}, {{ json_encode($target) }})" title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" action="{{ route('admin.targets.destroy', $target) }}"
                  onsubmit="return confirm('Delete this target and all its clients?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endforeach
</div>
@endif

{{-- ── Add Target Modal ──────────────────────────────────── --}}
<div class="modal fade" id="addTargetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bullseye me-2 text-primary"></i>New Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.targets.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Target Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                   placeholder="e.g. Close 20 Enterprise Clients – Q3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Target <span class="text-danger">*</span></label>
                            <input type="number" name="total_target" class="form-control" min="1" required placeholder="e.g. 20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Timeline (months) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="timeline_months" class="form-control" min="1" max="120" required placeholder="e.g. 3">
                                <span class="input-group-text">months</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this target…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes or strategy…"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-bullseye me-1"></i>Create Target</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Target Modal ─────────────────────────────────── --}}
<div class="modal fade" id="editTargetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Edit Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editTargetForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="etTitle" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Target <span class="text-danger">*</span></label>
                            <input type="number" name="total_target" id="etTotal" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Timeline (months) <span class="text-danger">*</span></label>
                            <input type="number" name="timeline_months" id="etMonths" class="form-control" min="1" max="120" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="etStart" class="form-control" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="etActive" value="1" checked>
                                <label class="form-check-label" for="etActive">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="etDesc" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="etNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditTarget(id, data) {
    document.getElementById('editTargetForm').action = '/admin/targets/' + id;
    document.getElementById('etTitle').value   = data.title || '';
    document.getElementById('etTotal').value   = data.total_target || '';
    document.getElementById('etMonths').value  = data.timeline_months || '';
    document.getElementById('etStart').value   = data.start_date || '';
    document.getElementById('etDesc').value    = data.description || '';
    document.getElementById('etNotes').value   = data.notes || '';
    document.getElementById('etActive').checked = data.is_active == 1;
    new bootstrap.Modal(document.getElementById('editTargetModal')).show();
}
</script>
@endpush
@endsection
