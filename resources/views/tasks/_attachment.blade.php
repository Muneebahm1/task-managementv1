<div class="attachment-thumb position-relative" id="att-{{ $att->id }}" style="text-decoration:none;">
    @if($att->isImage())
        <a href="{{ $att->url }}" target="_blank" style="display:block;width:100%;height:100%;">
            <img src="{{ $att->url }}" alt="{{ $att->original_name }}" style="width:100%;height:100%;object-fit:cover;">
        </a>
    @else
        <a href="{{ route('attachments.download', $att) }}" class="d-flex flex-column align-items-center justify-content-center w-100 h-100 text-decoration-none">
            <i class="bi {{ $att->icon }}" style="font-size:24px;color:#6B778C;"></i>
            <small style="font-size:10px;color:#6B778C;margin-top:4px;padding:0 4px;text-align:center;overflow:hidden;max-width:72px;white-space:nowrap;text-overflow:ellipsis;">{{ $att->original_name }}</small>
        </a>
    @endif
    @if(auth()->user()->isAdmin() || auth()->id() === $att->user_id)
    <button onclick="deleteAttachment({{ $att->id }})"
            style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:#BF2600;color:#fff;border:none;font-size:10px;display:flex;align-items:center;justify-content:center;padding:0;cursor:pointer;"
            title="Remove">×</button>
    @endif
</div>
