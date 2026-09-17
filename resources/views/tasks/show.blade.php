@extends('layouts.app')

@section('title', $task->task_key . ' — ' . $task->title)

@push('styles')
<style>
    .task-header { background:#fff; border:1px solid #DFE1E6; border-radius:10px; padding:24px 28px; margin-bottom:20px; }
    .task-body   { background:#fff; border:1px solid #DFE1E6; border-radius:10px; padding:24px 28px; }
    .section-title { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6B778C; margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid #DFE1E6; }
    .activity-time { font-size:11px; color:#9BA8B4; white-space:nowrap; }
    @keyframes recordingPulse { 0%,100%{opacity:1} 50%{opacity:.3} }
    .recording-pulse { animation: recordingPulse 1s ease-in-out infinite; }
</style>
@endpush

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="font-size:13px;">
        <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
        <li class="breadcrumb-item active">{{ $task->task_key }}</li>
    </ol>
</nav>

<div class="row g-4">
    <!-- LEFT: Main content -->
    <div class="col-lg-8">
        <!-- Task Header -->
        <div class="task-header">
            <div class="d-flex align-items-start gap-3 mb-3">
                <i class="{{ $task->type_icon }}" style="color:{{ $task->type_color }};font-size:22px;flex-shrink:0;margin-top:3px;"></i>
                <div class="flex-1">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="task-key">{{ $task->task_key }}</span>
                        <span class="status-pill" style="background:{{ $task->status->color }}" id="statusPill">
                            {{ $task->status->name }}
                        </span>
                        @if($task->isOverdue())
                        <span class="overdue-badge"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                        @endif
                    </div>

                    @if(auth()->user()->isAdmin())
                    <h1 contenteditable="false" id="taskTitle" class="h3 fw-700 mb-0" style="font-weight:700;line-height:1.3;color:#172B4D;">
                        {{ $task->title }}
                    </h1>
                    @else
                    <h1 class="h3 fw-700 mb-0" style="font-weight:700;line-height:1.3;color:#172B4D;">{{ $task->title }}</h1>
                    @endif
                </div>
            </div>

            <!-- Quick status changer — admin & employee only -->
            @if(!auth()->user()->isClient())
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span style="font-size:12px;color:#6B778C;">Change Status:</span>
                @foreach($statuses as $s)
                {{-- Hide cancel button for employees, show only for admins --}}
                @if($s->slug === 'cancelled' && !auth()->user()->isAdmin())
                    @continue
                @endif
                <button onclick="updateStatus({{ $task->id }}, {{ $s->id }}, '{{ $s->name }}', '{{ $s->color }}')"
                        class="btn btn-sm {{ $task->status_id == $s->id ? 'btn-primary' : 'btn-outline-secondary' }}"
                        style="font-size:11.5px;padding:3px 10px;border-radius:12px;{{ $task->status_id == $s->id ? 'background:'.$s->color.';border-color:'.$s->color.';' : '' }}"
                        id="status-btn-{{ $s->id }}">
                    {{ $s->name }}
                </button>
                @endforeach
            </div>
            @else
            {{-- Client: read-only status display with a helpful note --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span style="font-size:12px;color:#6B778C;"><i class="bi bi-info-circle me-1"></i>Current status:</span>
                <span class="status-pill" style="background:{{ $task->status->color }}">{{ $task->status->name }}</span>
                <span style="font-size:11px;color:#9BA8B4;">(managed by the team)</span>
            </div>
            @endif
        </div>

        <!-- Description -->
        <div class="task-body mb-4">
            <div class="section-title">Description</div>
            @if($task->description)
                <div style="font-size:14.5px;line-height:1.7;color:#172B4D;white-space:pre-wrap;">{{ $task->description }}</div>
            @else
                <p class="text-muted" style="font-size:14px;font-style:italic;">No description provided.</p>
            @endif

            @if(auth()->user()->isAdmin())
            <div class="mt-3">
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Edit Task
                </a>
            </div>
            @endif
        </div>

        <!-- Attachments -->
        <div class="task-body mb-4">
            <div class="section-title d-flex align-items-center justify-content-between">
                <span>Attachments ({{ $task->attachments->count() }})</span>
                <label for="quickUpload" class="btn btn-sm btn-outline-secondary mb-0" style="cursor:pointer;font-size:11px;">
                    <i class="bi bi-paperclip me-1"></i>Attach
                </label>
                <input type="file" id="quickUpload" multiple class="d-none">
            </div>

            <div id="attachmentList" class="d-flex flex-wrap gap-2">
                @forelse($task->attachments as $att)
                @include('tasks._attachment', ['att' => $att])
                @empty
                <p class="text-muted" style="font-size:13px;" id="noAttachMsg">No attachments yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Comments -->
        <div class="task-body mb-4">
            <div class="section-title">Activity ({{ $task->comments->count() }} comments)</div>

            <!-- Add comment -->
            <div class="d-flex gap-3 mb-4">
                <div class="user-avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0;">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    @else
                        {{ auth()->user()->initials }}
                    @endif
                </div>
                <div class="flex-1">
                    {{-- Text comment area --}}
                    <div id="textCommentArea">
                        <textarea id="commentInput" class="form-control" rows="3"
                                  placeholder="Add a comment… (Ctrl+Enter to submit)"></textarea>
                        <div class="mt-2 d-flex gap-2 align-items-center flex-wrap">
                            <button class="btn btn-primary btn-sm" onclick="submitComment()">
                                <i class="bi bi-send me-1"></i>Comment
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="$('#commentInput').val('')">Cancel</button>
                            <span class="text-muted mx-1" style="font-size:12px;">or</span>
                            <button type="button" id="voiceCommentBtn" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                <i class="bi bi-mic-fill"></i> Voice Note
                            </button>
                        </div>
                    </div>

                    {{-- Voice recording in-progress --}}
                    <div id="voiceCommentRecordUI" class="d-none p-3 rounded-3 d-flex align-items-center gap-3"
                         style="background:#fff5f5;border:1px solid #ffcdd2;">
                        <span class="badge bg-danger recording-pulse">REC</span>
                        <span id="voiceCommentTimer" style="font-weight:600;font-size:14px;color:#c62828;">0:00</span>
                        <button type="button" id="stopVoiceCommentBtn" class="btn btn-sm btn-danger ms-auto">
                            <i class="bi bi-stop-fill me-1"></i>Stop &amp; Send
                        </button>
                        <button type="button" id="cancelVoiceCommentBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div id="commentList">
                @forelse($task->comments as $comment)
                @include('tasks._comment', ['comment' => $comment])
                @empty
                <p class="text-muted text-center py-3" style="font-size:13px;" id="noCommentMsg">
                    No comments yet. Be the first to comment!
                </p>
                @endforelse
            </div>
        </div>

        <!-- Activity Log -->
        <div class="task-body">
            <div class="section-title">Activity Log</div>
            @forelse($task->activities as $activity)
            <div class="activity-item">
                <div class="mini-avatar" style="width:26px;height:26px;font-size:10px;flex-shrink:0;">
                    @if($activity->user->avatar)
                        <img src="{{ asset('storage/' . $activity->user->avatar) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    @else
                        {{ $activity->user->initials }}
                    @endif
                </div>
                <div>
                    <span style="font-size:13px;font-weight:600;">{{ $activity->user->name }}</span>
                    <span style="font-size:13px;color:#6B778C;"> {!! $activity->description !!}</span>
                    <span class="activity-time ms-2">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <p class="text-muted" style="font-size:13px;">No activity recorded.</p>
            @endforelse
        </div>
    </div>

    <!-- RIGHT: Details sidebar -->
    <div class="col-lg-4">
        <div class="card" style="position:sticky;top:76px;">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0" style="font-size:14px;font-weight:700;">Details</h6>
                @if(auth()->user()->isAdmin())
                <div class="d-flex gap-1">
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                          onsubmit="return confirm('Delete this task?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
                @endif
            </div>
            <div class="card-body p-3">
                <!-- Assignee -->
                <div class="mb-3">
                    <div class="detail-label">Assignee</div>
                    <div class="detail-value" id="assigneeDetail">
                        @if($task->assignee)
                        <div class="d-flex align-items-center gap-2">
                            <div class="mini-avatar">
                                @if($task->assignee->avatar)
                                    <img src="{{ asset('storage/' . $task->assignee->avatar) }}" alt="">
                                @else {{ $task->assignee->initials }} @endif
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;">{{ $task->assignee->name }}</div>
                                <div style="font-size:11px;color:#6B778C;">{{ $task->assignee->job_title ?? ucfirst($task->assignee->role) }}</div>
                            </div>
                        </div>
                        @else
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted" style="font-size:13px;" id="unassignedLabel">
                                <i class="bi bi-person me-1"></i>Unassigned
                            </span>
                            @if(!auth()->user()->isClient())
                            <button type="button" id="assignSelfBtn"
                                    class="btn btn-sm btn-outline-success d-flex align-items-center gap-1"
                                    style="font-size:11px;"
                                    onclick="assignSelfFromDetail({{ $task->id }})">
                                <i class="bi bi-person-check-fill"></i> Assign to Me
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Reporter -->
                <div class="mb-3">
                    <div class="detail-label">Reporter</div>
                    <div class="detail-value d-flex align-items-center gap-2">
                        <div class="mini-avatar">
                            @if($task->reporter->avatar)
                                <img src="{{ asset('storage/' . $task->reporter->avatar) }}" alt="">
                            @else {{ $task->reporter->initials }} @endif
                        </div>
                        <span style="font-size:13px;">{{ $task->reporter->name }}</span>
                    </div>
                </div>

                @if($task->isPendingApproval() || $task->isApproved())
                <hr style="border-color:#DFE1E6;margin:14px 0;">
                
                <!-- Approval Section -->
                <div class="mb-3">
                    <div class="detail-label">Approval Status</div>
                    <div class="detail-value">
                        @if($task->isPendingApproval())
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock-fill me-1"></i>Pending Approval
                            </span>
                        @elseif($task->isApproved())
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle-fill me-1"></i>Approved
                            </span>
                            @if($task->approvedBy)
                            <div class="mt-2" style="font-size:12px;color:#6B778C;">
                                Approved by {{ $task->approvedBy->name }}
                                @if($task->approved_at)
                                ({{ $task->approved_at->diffForHumans() }})
                                @endif
                            </div>
                            @endif
                        @endif
                    </div>
                </div>

                @if($task->review_notes)
                <div class="mb-3">
                    <div class="detail-label">Review Notes</div>
                    <div class="detail-value" style="font-size:12px;color:#6B778C;font-style:italic;">
                        "{{ $task->review_notes }}"
                    </div>
                </div>
                @endif

                @if(auth()->user()->isAdmin() && $task->isPendingApproval())
                <div class="mb-3">
                    <div class="detail-label">Admin Actions</div>
                    <div class="detail-value d-flex flex-column gap-2">
                        <form method="POST" action="{{ route('tasks.approve', $task) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100">
                                <i class="bi bi-check-circle-fill me-1"></i>Approve Task
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-sm btn-outline-warning w-100" data-bs-toggle="modal" data-bs-target="#sendForReviewModal">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Send for Review
                        </button>
                    </div>
                </div>
                @endif
                @endif

                <hr style="border-color:#DFE1E6;margin:14px 0;">

                <!-- Priority -->
                <div class="mb-3">
                    <div class="detail-label">Priority</div>
                    <div class="detail-value">
                        <span class="priority-badge" style="background:{{ $task->priority_color }}22;color:{{ $task->priority_color }}">
                            <i class="{{ $task->priority_icon }}"></i>
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                </div>

                <!-- Type -->
                <div class="mb-3">
                    <div class="detail-label">Type</div>
                    <div class="detail-value">
                        <span class="type-badge" style="background:{{ $task->type_color }}22;color:{{ $task->type_color }}">
                            <i class="{{ $task->type_icon }}"></i>
                            {{ ucfirst($task->type) }}
                        </span>
                    </div>
                </div>

                @if($task->due_date)
                <div class="mb-3">
                    <div class="detail-label">Due Date</div>
                    <div class="detail-value {{ $task->isOverdue() ? 'text-danger' : '' }}">
                        @if($task->isOverdue())<i class="bi bi-exclamation-triangle-fill me-1"></i>@endif
                        {{ $task->due_date->format('M d, Y') }}
                        @if(!$task->isOverdue())
                        <small class="text-muted">({{ $task->due_date->diffForHumans() }})</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($task->story_points)
                <div class="mb-3">
                    <div class="detail-label">Story Points</div>
                    <div class="detail-value fw-700">{{ $task->story_points }} SP</div>
                </div>
                @endif

                @if($task->estimated_hours)
                <div class="mb-3">
                    <div class="detail-label">Estimated Hours</div>
                    <div class="detail-value">{{ $task->estimated_hours }}h</div>
                </div>
                @endif

                @if($task->labels)
                <div class="mb-3">
                    <div class="detail-label">Labels</div>
                    <div class="detail-value d-flex flex-wrap gap-1 mt-1">
                        @foreach(explode(',', $task->labels) as $label)
                        <span class="badge bg-light text-secondary border">{{ trim($label) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <hr style="border-color:#DFE1E6;margin:14px 0;">

                <div class="mb-2">
                    <div class="detail-label">Created</div>
                    <div class="detail-value text-muted" style="font-size:12px;">{{ $task->created_at->format('M d, Y H:i') }}</div>
                </div>
                <div>
                    <div class="detail-label">Updated</div>
                    <div class="detail-value text-muted" style="font-size:12px;">{{ $task->updated_at->diffForHumans() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const taskId     = {{ $task->id }};
    const commentUrl = '/tasks/' + taskId + '/comments';
    const uploadUrl  = '/tasks/' + taskId + '/attachments';

    function assignSelfFromDetail(id) {
        $.ajax({
            url: '/tasks/' + id + '/assign-self',
            method: 'PATCH',
            success: function (res) {
                $('#assigneeDetail').html(
                    '<div class="d-flex align-items-center gap-2">' +
                    '<div class="mini-avatar" style="background:#0052CC;color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">' + res.assignee_initials + '</div>' +
                    '<div><div style="font-size:13px;font-weight:600;">' + res.assignee_name + '</div></div>' +
                    '</div>'
                );
            },
            error: function (xhr) { alert(xhr.responseJSON?.error || 'Failed to assign.'); }
        });
    }

    // Status update
    function updateStatus(taskId, statusId, statusName, statusColor) {
        $.ajax({
            url: '/tasks/' + taskId + '/status',
            method: 'PATCH',
            data: { status_id: statusId },
            success: function() {
                // Update pill
                $('#statusPill').text(statusName).css('background', statusColor);
                // Update buttons
                $('[id^=status-btn-]').each(function() {
                    const id = parseInt($(this).attr('id').replace('status-btn-', ''));
                    if (id === statusId) {
                        $(this).addClass('btn-primary').removeClass('btn-outline-secondary').css({ background: statusColor, borderColor: statusColor });
                    } else {
                        $(this).removeClass('btn-primary').addClass('btn-outline-secondary').css({ background: '', borderColor: '' });
                    }
                });
            }
        });
    }

    // Add text comment
    function submitComment() {
        const content = $('#commentInput').val().trim();
        if (!content) return;

        $.post(commentUrl, { content: content }, function(res) {
            if (res.success) appendComment(res.comment);
        });
    }

    function appendComment(c) {
        $('#noCommentMsg').remove();
        $('#commentInput').val('');
        const avatarHtml = '<div class="user-avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0;background:#0052CC;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">' + c.user.initials + '</div>';
        let bodyContent;
        if (c.is_voice && c.voice_url) {
            bodyContent = '<audio controls src="' + c.voice_url + '" style="height:36px;width:100%;max-width:360px;margin-top:4px;"></audio>';
        } else {
            bodyContent = c.content;
        }
        const voiceBadge = c.is_voice ? '<span class="badge bg-danger ms-1" style="font-size:9px;vertical-align:middle;"><i class="bi bi-mic-fill me-1"></i>Voice</span>' : '';
        const actions = c.can_edit && !c.is_voice
            ? '<div class="comment-actions mt-1"><a onclick="editComment(' + c.id + ')">Edit</a><a onclick="deleteComment(' + c.id + ')" class="text-danger">Delete</a></div>'
            : (c.can_edit ? '<div class="comment-actions mt-1"><a onclick="deleteComment(' + c.id + ')" class="text-danger">Delete</a></div>' : '');
        const html = '<div class="comment-item fade-in" id="comment-' + c.id + '">' +
            '<div>' + avatarHtml + '</div>' +
            '<div class="comment-body">' +
                '<div class="comment-meta"><span class="comment-author">' + c.user.name + '</span>' + voiceBadge +
                    '<span class="comment-time ms-2">' + c.created_at + '</span></div>' +
                '<div class="comment-content" id="comment-text-' + c.id + '">' + bodyContent + '</div>' +
                actions +
            '</div>' +
        '</div>';
        $('#commentList').append(html);
    }

    // ── Voice comment recording ─────────────────────────────────
    let vcRecorder = null, vcChunks = [], vcTimer = null, vcSeconds = 0, vcStream = null;
    const voiceCommentUrl = '/tasks/' + taskId + '/voice-comment';

    document.getElementById('voiceCommentBtn').addEventListener('click', async function () {
        try {
            vcStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const mimeType = MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/ogg';
            vcRecorder = new MediaRecorder(vcStream, { mimeType });
            vcChunks = [];
            vcRecorder.ondataavailable = e => vcChunks.push(e.data);
            vcRecorder.onstop = function () {
                vcStream.getTracks().forEach(t => t.stop());
                clearInterval(vcTimer); vcSeconds = 0;
                $('#voiceCommentRecordUI').addClass('d-none');
                $('#textCommentArea').removeClass('d-none');
                const blob = new Blob(vcChunks, { type: mimeType });
                const ext  = mimeType.includes('webm') ? 'webm' : 'ogg';
                const fd   = new FormData();
                fd.append('audio', blob, 'voice-comment.' + ext);
                $.ajax({
                    url: voiceCommentUrl, method: 'POST',
                    data: fd, processData: false, contentType: false,
                    success: function (res) { if (res.success) appendComment(res.comment); },
                    error: function () { alert('Failed to upload voice note.'); }
                });
            };
            vcRecorder.start();
            $('#textCommentArea').addClass('d-none');
            $('#voiceCommentRecordUI').removeClass('d-none');
            vcSeconds = 0;
            vcTimer = setInterval(() => {
                vcSeconds++;
                const m = Math.floor(vcSeconds / 60), s = vcSeconds % 60;
                $('#voiceCommentTimer').text(m + ':' + String(s).padStart(2,'0'));
            }, 1000);
        } catch (err) {
            alert('Microphone access denied or not available.');
        }
    });

    document.getElementById('stopVoiceCommentBtn').addEventListener('click', function () {
        if (vcRecorder && vcRecorder.state === 'recording') vcRecorder.stop();
    });

    document.getElementById('cancelVoiceCommentBtn').addEventListener('click', function () {
        if (vcRecorder && vcRecorder.state === 'recording') {
            vcRecorder.onstop = function() {
                if (vcStream) vcStream.getTracks().forEach(t => t.stop());
                clearInterval(vcTimer); vcSeconds = 0;
                $('#voiceCommentRecordUI').addClass('d-none');
                $('#textCommentArea').removeClass('d-none');
            };
            vcRecorder.stop();
        }
    });

    // Edit comment
    function editComment(id) {
        const el  = $('#comment-text-' + id);
        const txt = el.text().trim();
        el.html('<textarea class="form-control" id="edit-' + id + '" rows="3">' + txt + '</textarea>' +
                '<div class="mt-1 d-flex gap-2"><button class="btn btn-sm btn-primary" onclick="saveComment(' + id + ')">Save</button>' +
                '<button class="btn btn-sm btn-outline-secondary" onclick="cancelEdit(' + id + ',`' + txt.replace(/`/g,'\\`') + '`)">Cancel</button></div>');
    }

    function cancelEdit(id, txt) { $('#comment-text-' + id).text(txt); }

    function saveComment(id) {
        const content = $('#edit-' + id).val().trim();
        if (!content) return;
        $.ajax({ url: '/comments/' + id, method: 'PUT', data: { content: content }, success: function(res) {
            if (res.success) $('#comment-text-' + id).text(res.content);
        }});
    }

    function deleteComment(id) {
        if (!confirm('Delete this comment?')) return;
        $.ajax({ url: '/comments/' + id, method: 'DELETE', success: function(res) {
            if (res.success) $('#comment-' + id).fadeOut(300, function() { $(this).remove(); });
        }});
    }

    // Ctrl+Enter to submit comment
    $('#commentInput').on('keydown', function(e) {
        if (e.ctrlKey && e.which === 13) submitComment();
    });

    // File upload
    document.getElementById('quickUpload').addEventListener('change', function() {
        if (!this.files.length) return;
        const fd = new FormData();
        Array.from(this.files).forEach(f => fd.append('attachments[]', f));

        $.ajax({
            url: uploadUrl, method: 'POST', data: fd,
            processData: false, contentType: false,
            beforeSend: function() { showSpinner(); },
            success: function(res) {
                hideSpinner();
                $('#noAttachMsg').remove();
                res.attachments.forEach(function(a) {
                    const thumbHtml = '<div class="attachment-thumb position-relative" id="att-' + a.id + '">' +
                        (a.is_image ? '<a href="' + a.url + '" target="_blank"><img src="' + a.url + '" style="width:100%;height:100%;object-fit:cover;"></a>' :
                            '<a href="' + a.url + '" target="_blank" class="attachment-thumb text-decoration-none"><i class="bi ' + a.icon + '"></i><small style="font-size:10px;margin-top:4px;">' + a.original_name.slice(0,14) + '</small></a>') +
                        '<button onclick="deleteAttachment(' + a.id + ')" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:#BF2600;color:#fff;border:none;font-size:10px;display:flex;align-items:center;justify-content:center;padding:0;">×</button>' +
                        '</div>';
                    $('#attachmentList').append(thumbHtml);
                });
            },
            error: function() { hideSpinner(); alert('Upload failed.'); }
        });
    });

    function deleteAttachment(id) {
        if (!confirm('Remove this attachment?')) return;
        $.ajax({ url: '/attachments/' + id, method: 'DELETE', success: function(res) {
            if (res.success) $('#att-' + id).fadeOut(300, function() { $(this).remove(); });
        }});
    }
</script>
@endpush

<!-- Send for Review Modal -->
@if(auth()->user()->isAdmin() && $task->isPendingApproval())
<div class="modal fade" id="sendForReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Task for Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tasks.send-for-review', $task) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="review_notes" class="form-label">Review Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="review_notes" name="review_notes" rows="4" required placeholder="Please explain why this task needs to be reviewed..."></textarea>
                        <small class="text-muted">These notes will be sent to the assignee to help them understand what needs to be fixed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Send for Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
