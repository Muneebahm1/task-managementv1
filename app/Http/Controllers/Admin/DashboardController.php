<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskStatus;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $totalTasks     = Task::count();
        $totalUsers     = User::count();
        $openTasks      = Task::whereHas('status', fn($q) => $q->where('is_closed', false))->count();
        $closedTasks    = Task::whereHas('status', fn($q) => $q->where('is_closed', true))->count();
        $overdueTasks   = Task::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', fn($q) => $q->where('is_closed', false))
            ->count();

        $statuses        = TaskStatus::withCount('tasks')->orderBy('order')->get();
        $recentActivity  = TaskActivity::with(['user', 'task'])->latest()->limit(15)->get();
        $topAssignees    = User::withCount('assignedTasks')
            ->orderByDesc('assigned_tasks_count')->limit(5)->get();

        $tasksByType = Task::selectRaw('type, count(*) as count')
            ->groupBy('type')->pluck('count', 'type');

        return view('admin.dashboard', compact(
            'totalTasks', 'totalUsers', 'openTasks', 'closedTasks',
            'overdueTasks', 'statuses', 'recentActivity', 'topAssignees', 'tasksByType'
        ));
    }
}
