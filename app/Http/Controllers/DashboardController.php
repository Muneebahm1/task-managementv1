<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var User $user */
        $user     = Auth::user();
        $userId   = $user->id;
        $statuses = TaskStatus::orderBy('order')->get();

        $withRelations = fn($q) => $q->with([
            'status', 'assignee', 'reporter',
            'userView' => fn($q2) => $q2->where('user_id', $userId),
        ]);

        if ($user->isAdmin()) {
            $totalTasks      = Task::count();
            $myTasks         = Task::where('assignee_id', $userId)->count();
            $overdueTasks    = Task::whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereHas('status', fn($q) => $q->where('is_closed', false))
                ->count();
            $completedTasks  = Task::whereHas('status', fn($q) => $q->where('is_closed', true))->count();
            $totalUsers      = User::count();
            $tasksByStatus   = $statuses->map(fn($s) => [
                'name'  => $s->name,
                'color' => $s->color,
                'count' => $s->tasks()->count(),
            ]);
            $recentTasks     = Task::tap($withRelations)->latest()->limit(8)->get();
            $tasksByPriority = Task::selectRaw('priority, count(*) as count')
                ->groupBy('priority')->pluck('count', 'priority');

        } elseif ($user->isClient()) {
            $totalTasks      = Task::where('reporter_id', $userId)->count();
            $myTasks         = $totalTasks;
            $overdueTasks    = 0;
            $completedTasks  = Task::where('reporter_id', $userId)
                ->whereHas('status', fn($q) => $q->where('is_closed', true))->count();
            $totalUsers      = null;
            $tasksByStatus   = $statuses->map(fn($s) => [
                'name'  => $s->name,
                'color' => $s->color,
                'count' => $s->tasks()->where('reporter_id', $userId)->count(),
            ]);
            $recentTasks     = Task::where('reporter_id', $userId)
                ->tap($withRelations)->latest()->limit(8)->get();
            $tasksByPriority = Task::where('reporter_id', $userId)
                ->selectRaw('priority, count(*) as count')
                ->groupBy('priority')->pluck('count', 'priority');

        } else {
            // Employee: sees all tasks now (same as index)
            $totalTasks      = Task::count();
            $myTasks         = Task::where('assignee_id', $userId)->count();
            $overdueTasks    = Task::whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereHas('status', fn($q) => $q->where('is_closed', false))
                ->count();
            $completedTasks  = Task::whereHas('status', fn($q) => $q->where('is_closed', true))->count();
            $totalUsers      = null;
            $tasksByStatus   = $statuses->map(fn($s) => [
                'name'  => $s->name,
                'color' => $s->color,
                'count' => $s->tasks()->count(),
            ]);
            $recentTasks     = Task::tap($withRelations)->latest()->limit(8)->get();
            $tasksByPriority = Task::selectRaw('priority, count(*) as count')
                ->groupBy('priority')->pluck('count', 'priority');
        }

        return view('dashboard.index', compact(
            'totalTasks', 'myTasks', 'overdueTasks', 'completedTasks',
            'totalUsers', 'tasksByStatus', 'recentTasks', 'tasksByPriority', 'statuses'
        ));
    }
}