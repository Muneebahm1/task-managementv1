<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = ['title', 'description', 'total_target', 'timeline_months', 'start_date', 'notes', 'is_active'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'is_active'  => 'boolean',
        ];
    }

    public function clients()
    {
        return $this->hasMany(TargetClient::class)->orderBy('created_at', 'desc');
    }

    public function getEndDateAttribute(): Carbon
    {
        return $this->start_date->addMonths($this->timeline_months);
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->startOfDay()->diffInDays($this->end_date, false));
    }

    public function getClosedCountAttribute(): int
    {
        return $this->clients()->count();
    }

    public function getRemainingTargetAttribute(): int
    {
        return max(0, $this->total_target - $this->closed_count);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_target === 0) return 0;
        return min(100, (int) round(($this->closed_count / $this->total_target) * 100));
    }

    public function getIsExpiredAttribute(): bool
    {
        return now()->gt($this->end_date);
    }
}