@extends('layouts.app')

@section('title', $target->title)

@section('content')
@php
    $closed    = $target->clients->count();
    $pct       = $target->total_target > 0 ? min(100, round(($closed / $target->total_target) * 100)) : 0;
    $endDate   = $target->start_date->addMonths($target->timeline_months);
    $daysLeft  = max(0, now()->startOfDay()->diffInDays($endDate, false));
    $isExpired = now()->gt($endDate);
    $remaining = max(0, $target->total_target - $closed);
    $progressColor = $pct >= 100 ? '#36B37E' : ($pct >= 60 ? '#0052CC' : ($isExpired ? '#BF2600' : '#FF8C00'));
@endphp

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="font-size:13px;">
        <li class="breadcrumb-item"><a href="{{ route('admin.targets.index') }}">Targets</a></li>
        <li class="breadcrumb-item active">{{ $target->title }}</li>
    </ol>
</nav>

{{-- ── Summary cards ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#DEEBFF;color:#0052CC;"><i class="bi bi-bullseye"></i></div>
            <div class="stat-info">
                <div class="stat-value text-primary">{{ $target->total_target }}</div>
                <div class="stat-label">Total Target</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#E3FCEF;color:#006644;"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="color:#006644;" id="closedCount">{{ $closed }}</div>
                <div class="stat-label">Clients Closed</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:{{ $isExpired ? '#FFEBE6' : '#FFFAE6' }};color:{{ $isExpired ? '#BF2600' : '#974F0C' }};">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value" style="color:{{ $isExpired ? '#BF2600' : '#974F0C' }};" id="remainingCount">{{ $remaining }}</div>
                <div class="stat-label">Remaining</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#EAE6FF;color:#6554C0;"><i class="bi bi-calendar-event"></i></div>
            <div class="stat-info">
                <div class="stat-value" style="color:{{ $isExpired ? '#BF2600' : '#6554C0' }};">
                    {{ $isExpired ? 'Expired' : $daysLeft . 'd' }}
                </div>
                <div class="stat-label">Time Remaining</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Progress bar ──────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h5 style="font-weight:700;margin-bottom:2px;color:#172B4D;">{{ $target->title }}</h5>
                @if($target->description)
                <p style="font-size:13px;color:#6B778C;margin-bottom:0;">{{ $target->description }}</p>
                @endif
            </div>
            <div class="text-end">
                <div style="font-size:28px;font-weight:800;color:{{ $progressColor }};" id="progressPct">{{ $pct }}%</div>
                <small class="text-muted">{{ $target->start_date->format('M d, Y') }} → {{ $endDate->format('M d, Y') }} ({{ $target->timeline_months }} months)</small>
            </div>
        </div>
        <div class="progress mb-2" style="height:14px;border-radius:8px;background:#DFE1E6;">
            <div class="progress-bar" id="progressBar"
                 style="width:{{ $pct }}%;background:{{ $progressColor }};border-radius:8px;transition:width .6s ease;"></div>
        </div>
        <div class="d-flex justify-content-between" style="font-size:12px;color:#6B778C;">
            <span>{{ $closed }} / {{ $target->total_target }} closed</span>
            @if($isExpired)
            <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Target period has ended</span>
            @else
            <span><i class="bi bi-clock me-1"></i>{{ $daysLeft }} days remaining</span>
            @endif
        </div>
        @if($target->notes)
        <div class="mt-3 p-3 rounded-3" style="background:#F4F5F7;font-size:13px;color:#6B778C;">
            <i class="bi bi-sticky me-2"></i>{{ $target->notes }}
        </div>
        @endif
    </div>
</div>

{{-- ── Client List ───────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
        <span style="font-weight:700;font-size:14px;color:#172B4D;">
            <i class="bi bi-people-fill me-2 text-success"></i>Closed Clients
            <span class="badge bg-success ms-2" id="clientCountBadge">{{ $closed }}</span>
        </span>
        <button class="btn btn-sm btn-success d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addClientModal">
            <i class="bi bi-person-plus-fill"></i> Add Client
        </button>
    </div>
    <div class="card-body p-0">
        @if($target->clients->isEmpty())
        <div class="text-center py-5" id="noClientMsg">
            <i class="bi bi-person-dash" style="font-size:40px;color:#DFE1E6;"></i>
            <p class="text-muted mt-2">No clients added yet. Start closing!</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="clientTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Notes</th>
                        <th style="width:90px">Added</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="clientList">
                @foreach($target->clients as $i => $client)
                <tr id="client-row-{{ $client->id }}">
                    <td style="color:#6B778C;font-size:12px;">{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:28px;height:28px;border-radius:50%;background:#36B37E;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;font-size:13px;">{{ $client->name }}</span>
                        </div>
                    </td>
                    <td style="font-size:12px;">
                        @if($client->email) <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td style="font-size:12px;">{{ $client->phone ?: '—' }}</td>
                    <td style="font-size:12px;color:#6B778C;max-width:200px;">{{ $client->notes ? Str::limit($client->notes, 50) : '—' }}</td>
                    <td style="font-size:12px;color:#6B778C;">{{ $client->created_at->format('M d') }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger remove-client-btn"
                                data-id="{{ $client->id }}"
                                onclick="removeClient({{ $client->id }})" title="Remove">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ── Add Client Modal ──────────────────────────────────────── --}}
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2 text-success"></i>Add Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" id="clientName" class="form-control" placeholder="Full name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" id="clientEmail" class="form-control" placeholder="email@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" id="clientPhone" class="form-control" placeholder="+1 555 000 0000">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea id="clientNotes" class="form-control" rows="2" placeholder="Deal notes, referral info…"></textarea>
                    </div>
                    <div id="clientError" class="col-12 d-none">
                        <div class="alert alert-danger mb-0 py-2" style="font-size:13px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="addClientSubmit" onclick="addClient()">
                    <i class="bi bi-check-circle me-1"></i>Add Client
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const targetId    = {{ $target->id }};
const addClientUrl = '/admin/targets/' + targetId + '/clients';
let totalTarget   = {{ $target->total_target }};
let clientCount   = {{ $closed }};

function updateStats(stats) {
    document.getElementById('closedCount').textContent    = stats.closed_count;
    document.getElementById('remainingCount').textContent = stats.remaining_target;
    document.getElementById('progressPct').textContent    = stats.progress_percent + '%';
    document.getElementById('progressBar').style.width    = stats.progress_percent + '%';
    document.getElementById('clientCountBadge').textContent = stats.closed_count;
    clientCount = stats.closed_count;
}

function addClient() {
    const name  = document.getElementById('clientName').value.trim();
    const email = document.getElementById('clientEmail').value.trim();
    const phone = document.getElementById('clientPhone').value.trim();
    const notes = document.getElementById('clientNotes').value.trim();

    if (!name) { showClientError('Client name is required.'); return; }

    document.getElementById('addClientSubmit').disabled = true;

    $.ajax({
        url: addClientUrl, method: 'POST',
        data: { name, email, phone, notes },
        success: function (res) {
            document.getElementById('addClientSubmit').disabled = false;
            bootstrap.Modal.getInstance(document.getElementById('addClientModal')).hide();
            clearClientForm();

            const c   = res.client;
            const idx = clientCount + 1;
            const row = `<tr id="client-row-${c.id}">
                <td style="color:#6B778C;font-size:12px;">${idx}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:28px;height:28px;border-radius:50%;background:#36B37E;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;">
                            ${c.name.charAt(0).toUpperCase()}
                        </div>
                        <span style="font-weight:600;font-size:13px;">${c.name}</span>
                    </div>
                </td>
                <td style="font-size:12px;">${c.email ? '<a href="mailto:'+c.email+'">'+c.email+'</a>' : '—'}</td>
                <td style="font-size:12px;">${c.phone || '—'}</td>
                <td style="font-size:12px;color:#6B778C;">${c.notes ? c.notes.slice(0,50) : '—'}</td>
                <td style="font-size:12px;color:#6B778C;">Just now</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeClient(${c.id})" title="Remove">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>`;

            $('#noClientMsg').remove();
            if (!$('#clientList').length) {
                // Build table if it didn't exist
                location.reload();
                return;
            }
            $('#clientList').prepend(row);
            updateStats(res.stats);
        },
        error: function (xhr) {
            document.getElementById('addClientSubmit').disabled = false;
            showClientError(xhr.responseJSON?.message || 'Failed to add client.');
        }
    });
}

function removeClient(id) {
    if (!confirm('Remove this client from the target?')) return;
    $.ajax({
        url: '/admin/target-clients/' + id, method: 'DELETE',
        success: function () {
            $('#client-row-' + id).fadeOut(200, function () {
                $(this).remove();
                clientCount = Math.max(0, clientCount - 1);
                const remaining = Math.max(0, totalTarget - clientCount);
                const pct = totalTarget > 0 ? Math.min(100, Math.round((clientCount / totalTarget) * 100)) : 0;
                updateStats({
                    closed_count: clientCount,
                    remaining_target: remaining,
                    progress_percent: pct,
                    days_remaining: 0,
                });
                $('#clientCountBadge').text(clientCount);
            });
        },
        error: function () { alert('Failed to remove client.'); }
    });
}

function showClientError(msg) {
    const $err = $('#clientError');
    $err.find('.alert').text(msg);
    $err.removeClass('d-none');
}

function clearClientForm() {
    ['clientName','clientEmail','clientPhone','clientNotes'].forEach(id => {
        document.getElementById(id).value = '';
    });
    $('#clientError').addClass('d-none');
}
</script>
@endpush
@endsection
