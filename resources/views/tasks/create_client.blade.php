@extends('layouts.app')
@section('title', 'Submit a Request')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Submit a Request</h1>
        <p class="page-subtitle">Describe what you need and our team will pick it up</p>
    </div>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> My Requests
    </a>
</div>

<!-- Client info banner -->
<div class="alert alert-info d-flex align-items-center gap-3 mb-4" style="background:#DEEBFF;border:none;border-left:4px solid #0052CC;border-radius:8px;">
    <i class="bi bi-info-circle-fill" style="font-size:20px;color:#0052CC;flex-shrink:0;"></i>
    <div style="font-size:13.5px;color:#0747A6;">
        Your request will be submitted as <strong>unassigned</strong> and picked up by our team.
        You'll be able to track its progress and add comments from your dashboard.
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-circle-fill me-2"></i>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <!-- Main form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Request Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Summary / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg"
                               placeholder="Brief summary of what you need…"
                               value="{{ old('title') }}" required
                               style="font-size:16px;font-weight:600;">
                        <div class="form-text">Keep it short and descriptive</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="8"
                                  placeholder="Please describe in detail:&#10;• What exactly do you need?&#10;• Any specific requirements or constraints?&#10;• Expected outcome or deliverable?"
                                  required>{{ old('description') }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Request Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                @php
                                    $types = [
                                        'task'        => ['Task / General Work', 'bi-check2-square', '#0052CC'],
                                        'bug'         => ['Bug / Issue Report',  'bi-bug-fill',      '#FF5630'],
                                        'feature'     => ['New Feature',         'bi-stars',         '#36B37E'],
                                        'improvement' => ['Improvement',         'bi-arrow-up-right-circle-fill', '#FF8C00'],
                                    ];
                                @endphp
                                @foreach($types as $key => [$label, $icon, $color])
                                <option value="{{ $key }}" {{ old('type', 'task') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Urgency / Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low"     {{ old('priority') === 'low'     ? 'selected' : '' }}>Low — when convenient</option>
                                <option value="medium"  {{ old('priority', 'medium') === 'medium'  ? 'selected' : '' }}>Medium — normal pace</option>
                                <option value="high"    {{ old('priority') === 'high'    ? 'selected' : '' }}>High — soon as possible</option>
                                <option value="highest" {{ old('priority') === 'highest' ? 'selected' : '' }}>Critical — urgent!</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;">
                        <i class="bi bi-paperclip me-2"></i>Attachments <small class="text-muted fw-normal">(optional)</small>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="dropZone"
                         style="border:2px dashed #DFE1E6;border-radius:10px;padding:28px;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;"
                         onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-cloud-upload" style="font-size:36px;color:#DFE1E6;"></i>
                        <p class="mb-1 mt-2" style="font-size:14px;color:#6B778C;">Click to upload or drag & drop</p>
                        <p class="mb-0" style="font-size:12px;color:#9BA8B4;">Images, PDFs, Documents — max 20MB each</p>
                    </div>
                    <input type="file" name="attachments[]" id="fileInput" multiple class="d-none">
                    <div id="filePreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar summary -->
        <div class="col-lg-4">
            <div class="card" style="position:sticky;top:76px;">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;">Request Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="detail-label">Submitted By</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <div class="mini-avatar" style="width:30px;height:30px;font-size:11px;">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="">
                                @else {{ auth()->user()->initials }} @endif
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;">{{ auth()->user()->name }}</div>
                                <div style="font-size:11px;color:#6B778C;">{{ auth()->user()->job_title ?? 'Client' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Initial Status</div>
                        <div class="mt-1">
                            @php $default = \App\Models\TaskStatus::getDefault(); @endphp
                            <span class="status-pill" style="background:{{ $default->color }}">
                                {{ $default->name }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="detail-label">Assigned To</div>
                        <div style="font-size:13px;color:#6B778C;margin-top:4px;">
                            <i class="bi bi-people me-1"></i>Team will be assigned by admin
                        </div>
                    </div>

                    <hr style="border-color:#DFE1E6;">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Submit Request
                        </button>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const dropZone  = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const preview   = document.getElementById('filePreview');

    ['dragenter','dragover'].forEach(e => dropZone.addEventListener(e, ev => {
        ev.preventDefault();
        dropZone.style.borderColor = '#0052CC';
        dropZone.style.background  = '#DEEBFF';
    }));
    ['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => {
        ev.preventDefault();
        dropZone.style.borderColor = '#DFE1E6';
        dropZone.style.background  = '';
    }));
    dropZone.addEventListener('drop', ev => {
        fileInput.files = ev.dataTransfer.files;
        renderPreviews(ev.dataTransfer.files);
    });
    fileInput.addEventListener('change', () => renderPreviews(fileInput.files));

    function renderPreviews(files) {
        preview.innerHTML = '';
        Array.from(files).forEach(function(f) {
            const thumb = document.createElement('div');
            thumb.className = 'attachment-thumb';
            if (f.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                thumb.appendChild(img);
            } else {
                thumb.innerHTML = '<i class="bi bi-file-earmark" style="font-size:24px;color:#6B778C;"></i>' +
                    '<small style="font-size:10px;margin-top:4px;padding:0 4px;text-align:center;max-width:72px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">' + f.name + '</small>';
            }
            preview.appendChild(thumb);
        });
    }
</script>
@endpush
