@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
@php $authUser = auth()->user(); @endphp
<div class="page-header">
    <div>
        <h1 class="page-title">Contacts</h1>
        <p class="page-subtitle">{{ $contacts->total() }} contact(s) in your address book</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addContactModal">
        <i class="bi bi-person-plus-fill"></i> Add Contact
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Search -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.contacts.index') }}" id="contactSearch">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#6B778C;font-size:14px;"></i>
                        <input type="text" name="search" class="form-control" style="padding-left:32px;"
                               placeholder="Search by name, email or phone…" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Search</button>
                    @if(request('search'))
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Contacts Table -->
<div class="card">
    <div class="card-body p-0">
        @if($contacts->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-person-lines-fill" style="font-size:48px;color:#DFE1E6;"></i>
            <p class="text-muted mt-3">No contacts yet. Add your first one!</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Education Level</th>
                        <th>Description</th>
                        <th style="width:90px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($contacts as $i => $contact)
                <tr>
                    <td style="color:#6B778C;font-size:12px;">{{ $contacts->firstItem() + $i }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="mini-avatar" style="background:#0052CC;color:#fff;border-radius:50%;width:32px;height:32px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;font-size:13.5px;">{{ $contact->name }}</span>
                        </div>
                    </td>
                    <td style="font-size:13px;">
                        @if($contact->email)
                        <a href="mailto:{{ $contact->email }}" style="color:#0052CC;">{{ $contact->email }}</a>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td style="font-size:13px;">
                        @if($contact->phone)
                        <a href="tel:{{ $contact->phone }}" style="color:#172B4D;">{{ $contact->phone }}</a>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td style="font-size:13px;">
                        @if($contact->education_level)
                        <span class="badge bg-light text-secondary border">{{ $contact->education_level }}</span>
                        @else <span class="text-muted">—</span> @endif
                    </td>
                    <td style="font-size:12px;color:#6B778C;max-width:200px;">
                        {{ $contact->description ? Str::limit($contact->description, 60) : '—' }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="Edit"
                                    onclick="openEditContact({{ $contact->id }}, {{ json_encode($contact) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}"
                                  onsubmit="return confirm('Delete {{ $contact->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($contacts->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-between">
            <span style="font-size:13px;color:#6B778C;">
                Showing {{ $contacts->firstItem() }}–{{ $contacts->lastItem() }} of {{ $contacts->total() }}
            </span>
            {{ $contacts->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

{{-- ── Add Contact Modal ────────────────────────────────────── --}}
<div class="modal fade" id="addContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Add Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.contacts.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 555 000 0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Education Level</label>
                            <input type="text" name="education_level" class="form-control"
                                   placeholder="e.g. Bachelor's Degree, MBA…">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Optional notes about this contact…"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Add Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Contact Modal ───────────────────────────────────── --}}
<div class="modal fade" id="editContactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Edit Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editContactForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="editPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Education Level</label>
                            <input type="text" name="education_level" id="editEducation" class="form-control"
                                   placeholder="e.g. Bachelor's Degree, MBA…">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
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

@push('scripts')
<script>
function openEditContact(id, data) {
    document.getElementById('editContactForm').action   = '/admin/contacts/' + id;
    document.getElementById('editName').value           = data.name || '';
    document.getElementById('editEmail').value          = data.email || '';
    document.getElementById('editPhone').value          = data.phone || '';
    document.getElementById('editEducation').value      = data.education_level || '';
    document.getElementById('editDescription').value    = data.description || '';
    new bootstrap.Modal(document.getElementById('editContactModal')).show();
}
</script>
@endpush
@endsection
