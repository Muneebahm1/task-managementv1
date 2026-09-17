<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['task_id', 'user_id', 'content', 'is_edited', 'voice_path', 'is_voice'];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'is_voice'  => 'boolean',
        ];
    }

    public function getVoiceUrlAttribute(): ?string
    {
        return $this->voice_path ? asset('storage/' . $this->voice_path) : null;
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
