<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskView extends Model
{
    public $timestamps = false;

    protected $fillable = ['task_id', 'user_id', 'last_viewed_at'];

    protected function casts(): array
    {
        return ['last_viewed_at' => 'datetime'];
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