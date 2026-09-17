<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetClient extends Model
{
    protected $fillable = ['target_id', 'name', 'email', 'phone', 'notes'];

    public function target()
    {
        return $this->belongsTo(Target::class);
    }
}