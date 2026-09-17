<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_key', 'title', 'description', 'type', 'priority',
        'status_id', 'reporter_id', 'assignee_id',
        'due_date', 'story_points', 'estimated_hours', 'logged_hours', 'labels',
        'last_activity_at', 'approved_by', 'approved_at', 'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'         => 'date',
            'last_activity_at' => 'datetime',
            'approved_at'      => 'datetime',
        ];
    }

    public static function generateKey(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $num  = $last ? ($last->id + 1) : 1;
        return 'TASK-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function touchActivity(): void
    {
        $this->updateQuietly(['last_activity_at' => now()]);
    }

    // ── Relationships ──────────────────────────────────────────

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class)->orderBy('created_at', 'desc');
    }

    public function views()
    {
        return $this->hasMany(TaskView::class);
    }

    // ── Unread logic ────────────────────────────────────────────

    /**
     * Returns true if this task has new activity the given user hasn't seen.
     * Requires the 'userView' relationship to be eager-loaded (filtered to that user).
     */
    public function isUnreadFor(int $userId): bool
    {
        if (!$this->last_activity_at) {
            return false;
        }
        // 'userView' is loaded as a single-item collection via the eager load filter
        $view = $this->relationLoaded('userView')
            ? $this->userView->first()
            : $this->views()->where('user_id', $userId)->first();

        if (!$view) {
            return true; // never viewed but has activity
        }

        return $this->last_activity_at->gt($view->last_viewed_at);
    }

    // Relationship used for eager loading: ->with(['userView' => fn($q) => $q->where('user_id', $id)])
    public function userView()
    {
        return $this->hasMany(TaskView::class);
    }

    // ── Attribute helpers ───────────────────────────────────────

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'highest' => '#FF0000',
            'high'    => '#FF8C00',
            'medium'  => '#FFA500',
            'low'     => '#2684FF',
            'lowest'  => '#00B8D9',
            default   => '#6c757d',
        };
    }

    public function getPriorityIconAttribute(): string
    {
        return match($this->priority) {
            'highest' => 'bi-arrow-up-circle-fill',
            'high'    => 'bi-arrow-up-circle',
            'medium'  => 'bi-dash-circle',
            'low'     => 'bi-arrow-down-circle',
            'lowest'  => 'bi-arrow-down-circle-fill',
            default   => 'bi-dash-circle',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'bug'         => 'bi-bug-fill',
            'feature'     => 'bi-stars',
            'story'       => 'bi-book-fill',
            'epic'        => 'bi-lightning-fill',
            'improvement' => 'bi-arrow-up-right-circle-fill',
            default       => 'bi-check2-square',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'bug'         => '#FF5630',
            'feature'     => '#36B37E',
            'story'       => '#00B8D9',
            'epic'        => '#6554C0',
            'improvement' => '#FF8C00',
            default       => '#0052CC',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->status->is_closed;
    }

    public function isPendingApproval(): bool
    {
        return $this->status->slug === 'pending-approval';
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null && $this->approved_at !== null;
    }
}