<div class="comment-item fade-in" id="comment-{{ $comment->id }}">
    <div class="user-avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;background:#0052CC;">
        @if($comment->user->avatar)
            <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
        @else
            {{ $comment->user->initials }}
        @endif
    </div>
    <div class="comment-body">
        <div class="comment-meta">
            <span class="comment-author">{{ $comment->user->name }}</span>
            @if($comment->is_voice)
                <span class="badge bg-danger ms-1" style="font-size:9px;vertical-align:middle;">
                    <i class="bi bi-mic-fill me-1"></i>Voice
                </span>
            @endif
            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
            @if($comment->is_edited)
                <span class="comment-time">(edited)</span>
            @endif
        </div>
        <div class="comment-content" id="comment-text-{{ $comment->id }}">
            @if($comment->is_voice && $comment->voice_url)
                <audio controls src="{{ $comment->voice_url }}"
                       style="height:36px;width:100%;max-width:360px;margin-top:4px;"></audio>
            @else
                {{ $comment->content }}
            @endif
        </div>
        @if((auth()->id() === $comment->user_id || auth()->user()->isAdmin()) && !$comment->is_voice)
        <div class="comment-actions mt-1">
            <a onclick="editComment({{ $comment->id }})">Edit</a>
            <a onclick="deleteComment({{ $comment->id }})" class="text-danger">Delete</a>
        </div>
        @elseif(auth()->id() === $comment->user_id || auth()->user()->isAdmin())
        <div class="comment-actions mt-1">
            <a onclick="deleteComment({{ $comment->id }})" class="text-danger">Delete</a>
        </div>
        @endif
    </div>
</div>