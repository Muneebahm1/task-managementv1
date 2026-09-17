<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'icon', 'category', 'order', 'is_default', 'is_closed'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'backlog' => 'Backlog',
            'active'  => 'In Progress',
            'done'    => 'Done',
            default   => ucfirst($this->category),
        };
    }

    public static function getDefault(): self
    {
        return static::where('is_default', true)->first()
            ?? static::orderBy('order')->first();
    }
}
