@extends('layouts.app')

@php /** @var \App\Models\User $authUser */ $authUser = auth()->user(); @endphp

@section('title', 'Create Task')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Task</h1>
        <p class="page-subtitle">Fill in the details below to create a new task</p>
    </div>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-circle-fill me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data" id="taskForm">
    @csrf
    <div class="row g-4">
        <!-- Main content -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;">Task Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg"
                               placeholder="What needs to be done?" value="{{ old('title') }}" required
                               style="font-size:16px;font-weight:600;">
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center justify-content-between">
                            <span>Description</span>
                            <button type="button" id="voiceBtn" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                    title="Record voice note for description">
                                <i class="bi bi-mic-fill"></i>
                                <span id="voiceBtnText">Record</span>
                            </button>
                        </label>

                        {{-- Voice recording UI --}}
                        <div id="voiceRecordingUI" class="d-none mb-2 p-3 rounded-3 d-flex align-items-center gap-3"
                             style="background:#fff5f5;border:1px solid #ffcdd2;">
                            <span class="badge bg-danger recording-pulse">REC</span>
                            <span id="voiceTimer" style="font-weight:600;font-size:14px;color:#c62828;">0:00</span>
                            <button type="button" id="stopVoiceBtn" class="btn btn-sm btn-danger ms-auto">
                                <i class="bi bi-stop-fill me-1"></i>Stop
                            </button>
                        </div>

                        {{-- Voice preview --}}
                        <div id="voicePreview" class="d-none mb-2 p-2 rounded-3 d-flex align-items-center gap-2"
                             style="background:#e8f5e9;border:1px solid #a5d6a7;">
                            <i class="bi bi-mic-fill text-success"></i>
                            <span style="font-size:13px;font-weight:500;">Voice note recorded</span>
                            <audio id="voicePlayback" controls class="ms-2" style="height:32px;flex:1;"></audio>
                            <button type="button" id="discardVoice" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <textarea name="description" id="description" class="form-control" rows="8"
                                  placeholder="Add a description…">{{ old('description') }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                @php $types = ['task'=>['Task','bi-check2-square','#0052CC'],'bug'=>['Bug','bi-bug-fill','#FF5630'],'feature'=>['Feature','bi-stars','#36B37E'],'story'=>['Story','bi-book-fill','#00B8D9'],'epic'=>['Epic','bi-lightning-fill','#6554C0'],'improvement'=>['Improvement','bi-arrow-up-right-circle-fill','#FF8C00']]; @endphp
                                @foreach($types as $key => [$label, $icon, $color])
                                <option value="{{ $key }}" {{ old('type', 'task') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @php $priorities = ['highest'=>'Highest','high'=>'High','medium'=>'Medium','low'=>'Low','lowest'=>'Lowest']; @endphp
                                @foreach($priorities as $key => $label)
                                <option value="{{ $key }}" {{ old('priority', 'medium') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;"><i class="bi bi-paperclip me-2"></i>Attachments</h6>
                </div>
                <div class="card-body">
                    <div class="border-2 border-dashed rounded-3 p-4 text-center" id="dropZone"
                         style="border:2px dashed #DFE1E6;cursor:pointer;transition:border-color .2s;"
                         onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-cloud-upload" style="font-size:36px;color:#DFE1E6;"></i>
                        <p class="mb-1 mt-2" style="font-size:14px;color:#6B778C;">Click to upload or drag & drop files here</p>
                        <p class="mb-0" style="font-size:12px;color:#9BA8B4;">Max 20MB per file. Images, PDFs, Docs, Zip…</p>
                    </div>
                    <input type="file" name="attachments[]" id="fileInput" multiple class="d-none">
                    <div id="filePreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0" style="font-size:14px;font-weight:700;">Planning</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status_id" class="form-select" required>
                            @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('status_id', $statuses->where('is_default', true)->first()?->id) == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assignee</label>
                        <select name="assignee_id" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assignee_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ ucfirst($user->role) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                    </div>

                    @if($authUser->isAdmin())
                    <div class="mb-3 p-3 rounded-3" style="background:#FFF8E1;border:1px solid #FFE082;">
                        <label class="form-label d-flex align-items-center gap-2 mb-2" style="font-size:13px;font-weight:600;color:#974F0C;">
                            <i class="bi bi-clock-history"></i> Back Date Task
                        </label>
                        <input type="date" name="task_date" class="form-control form-control-sm"
                               value="{{ old('task_date') }}"
                               placeholder="Override created date">
                        <small class="text-muted mt-1 d-block" style="font-size:11px;">Leave empty to use today's date</small>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Story Points</label>
                        <input type="number" name="story_points" class="form-control" min="0" max="100"
                               placeholder="e.g. 5" value="{{ old('story_points') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estimated Hours</label>
                        <input type="number" name="estimated_hours" class="form-control" min="0"
                               placeholder="e.g. 8" value="{{ old('estimated_hours') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Labels <small class="text-muted">(comma-separated)</small></label>
                        <input type="text" name="labels" class="form-control"
                               placeholder="e.g. frontend, api, urgent" value="{{ old('labels') }}">
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create Task
                        </button>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden input to carry voice blob as base64 --}}
<input type="hidden" name="description_voice_data" id="voiceDataInput">
<input type="hidden" name="description_voice_mime" id="voiceMimeInput">
@endsection

@push('scripts')
<style>
@keyframes recordingPulse { 0%,100%{opacity:1} 50%{opacity:.3} }
.recording-pulse { animation: recordingPulse 1s ease-in-out infinite; }
</style>
<script>
// ── Drag-and-drop file upload zone ─────────────────────────────
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const preview   = document.getElementById('filePreview');

['dragenter','dragover'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.style.borderColor = '#0052CC'; }));
['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.style.borderColor = '#DFE1E6'; }));
dropZone.addEventListener('drop', ev => { fileInput.files = ev.dataTransfer.files; renderPreviews(ev.dataTransfer.files); });
fileInput.addEventListener('change', () => renderPreviews(fileInput.files));

function renderPreviews(files) {
    preview.innerHTML = '';
    Array.from(files).forEach(function(f) {
        const thumb = document.createElement('div');
        thumb.className = 'attachment-thumb';
        if (f.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            img.style.width = '100%'; img.style.height = '100%'; img.style.objectFit = 'cover';
            thumb.appendChild(img);
        } else {
            thumb.innerHTML = '<i class="bi bi-file-earmark" style="font-size:24px;color:#6B778C;"></i><small style="margin-top:4px;text-align:center;padding:0 4px;font-size:10px;overflow:hidden;">' + f.name.slice(0,14) + '</small>';
        }
        preview.appendChild(thumb);
    });
}

// ── Voice recording for description ────────────────────────────
let mediaRecorder = null, audioChunks = [], voiceBlob = null, timerInterval = null, seconds = 0;

const voiceBtn       = document.getElementById('voiceBtn');
const voiceBtnText   = document.getElementById('voiceBtnText');
const recordingUI    = document.getElementById('voiceRecordingUI');
const voiceTimer     = document.getElementById('voiceTimer');
const stopVoiceBtn   = document.getElementById('stopVoiceBtn');
const voicePreview   = document.getElementById('voicePreview');
const voicePlayback  = document.getElementById('voicePlayback');
const discardVoice   = document.getElementById('discardVoice');
const voiceDataInput = document.getElementById('voiceDataInput');
const voiceMimeInput = document.getElementById('voiceMimeInput');
const descTextarea   = document.getElementById('description');

voiceBtn.addEventListener('click', async function () {
    if (mediaRecorder && mediaRecorder.state === 'recording') return;
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        const mimeType = MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/ogg';
        mediaRecorder = new MediaRecorder(stream, { mimeType });
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = function () {
            voiceBlob = new Blob(audioChunks, { type: mimeType });
            voicePlayback.src = URL.createObjectURL(voiceBlob);
            // Store as base64 so it survives form submission without extra XHR
            const reader = new FileReader();
            reader.onloadend = () => {
                voiceDataInput.value = reader.result;
                voiceMimeInput.value = mimeType;
            };
            reader.readAsDataURL(voiceBlob);
            stream.getTracks().forEach(t => t.stop());
            recordingUI.classList.add('d-none');
            voicePreview.classList.remove('d-none');
            voiceBtn.classList.add('d-none');
            clearInterval(timerInterval); seconds = 0;
        };
        mediaRecorder.start();
        // Show UI
        voicePreview.classList.add('d-none');
        recordingUI.classList.remove('d-none');
        voiceBtnText.textContent = 'Recording…';
        voiceBtn.disabled = true;
        seconds = 0;
        timerInterval = setInterval(() => {
            seconds++;
            const m = Math.floor(seconds / 60), s = seconds % 60;
            voiceTimer.textContent = m + ':' + String(s).padStart(2,'0');
        }, 1000);
    } catch (err) {
        alert('Microphone access denied or not available.');
    }
});

stopVoiceBtn.addEventListener('click', function () {
    if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
});

discardVoice.addEventListener('click', function () {
    voiceBlob = null;
    voiceDataInput.value = '';
    voiceMimeInput.value = '';
    voicePlayback.src = '';
    voicePreview.classList.add('d-none');
    voiceBtn.classList.remove('d-none');
    voiceBtn.disabled = false;
    voiceBtnText.textContent = 'Record';
});
</script>
@endpush