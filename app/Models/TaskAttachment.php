<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'original_name', 'file_path', 'mime_type', 'file_size'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function getIconAttribute(): string
    {
        $type = $this->mime_type ?? '';
        if (str_starts_with($type, 'image/')) return 'bi-file-image';
        if ($type === 'application/pdf') return 'bi-file-pdf';
        if (str_contains($type, 'word')) return 'bi-file-word';
        if (str_contains($type, 'excel') || str_contains($type, 'spreadsheet')) return 'bi-file-excel';
        if (str_contains($type, 'zip') || str_contains($type, 'rar')) return 'bi-file-zip';
        return 'bi-file-earmark';
    }
}
