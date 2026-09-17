<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskActivity extends Model
{
    protected $fillable = ['task_id', 'user_id', 'action', 'field', 'old_value', 'new_value'];

    public $updatedAt = false;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDescriptionAttribute(): string
    {
        return match($this->action) {
            'created'        => 'created this task',
            'status_changed' => "changed status from <strong>{$this->old_value}</strong> to <strong>{$this->new_value}</strong>",
            'assigned'       => "assigned to <strong>{$this->new_value}</strong>",
            'priority_changed' => "changed priority from <strong>{$this->old_value}</strong> to <strong>{$this->new_value}</strong>",
            'commented'      => 'added a comment',
            'attached'       => "attached <strong>{$this->new_value}</strong>",
            'deleted_attachment' => "removed attachment <strong>{$this->old_value}</strong>",
            'updated'        => "updated <strong>{$this->field}</strong>",
            default          => $this->action,
        };
    }
}
