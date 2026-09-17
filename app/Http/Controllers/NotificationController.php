<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function deadlines(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Task::with(['status', 'assignee'])
            ->whereNotNull('due_date')
            ->whereHas('status', fn($q) => $q->where('is_closed', false))
            ->orderBy('due_date', 'asc');

        if ($user->isAdmin()) {
            // Admin sees all tasks with deadlines
        } elseif ($user->isClient()) {
            // Clients see tasks they submitted
            $query->where('reporter_id', $user->id);
        } else {
            // Employees see their assigned tasks
            $query->where('assignee_id', $user->id);
        }

        $tasks = $query->get();

        $notifications = [];

        foreach ($tasks as $task) {
            $daysUntilDue = now()->startOfDay()->diffInDays($task->due_date->startOfDay(), false);

            if ($daysUntilDue < 0) {
                $notifications[] = [
                    'id'       => 'task-' . $task->id,
                    'task_key' => $task->task_key,
                    'title'    => $task->title,
                    'url'      => route('tasks.show', $task),
                    'due_date' => $task->due_date->format('M d, Y'),
                    'days'     => abs((int) $daysUntilDue),
                    'level'    => 'overdue',
                    'label'    => 'Overdue by ' . abs((int) $daysUntilDue) . ' day(s)',
                    'color'    => '#FF5630',
                    'icon'     => 'bi-exclamation-triangle-fill',
                    'assignee' => $task->assignee?->name,
                ];
            } elseif ($daysUntilDue === 0) {
                $notifications[] = [
                    'id'       => 'task-' . $task->id,
                    'task_key' => $task->task_key,
                    'title'    => $task->title,
                    'url'      => route('tasks.show', $task),
                    'due_date' => $task->due_date->format('M d, Y'),
                    'days'     => 0,
                    'level'    => 'today',
                    'label'    => 'Due today!',
                    'color'    => '#FF8C00',
                    'icon'     => 'bi-alarm-fill',
                    'assignee' => $task->assignee?->name,
                ];
            } elseif ($daysUntilDue <= 2) {
                $notifications[] = [
                    'id'       => 'task-' . $task->id,
                    'task_key' => $task->task_key,
                    'title'    => $task->title,
                    'url'      => route('tasks.show', $task),
                    'due_date' => $task->due_date->format('M d, Y'),
                    'days'     => (int) $daysUntilDue,
                    'level'    => 'soon',
                    'label'    => 'Due in ' . (int) $daysUntilDue . ' day(s)',
                    'color'    => '#FFC400',
                    'icon'     => 'bi-clock-fill',
                    'assignee' => $task->assignee?->name,
                ];
            }
        }

        return response()->json([
            'count'         => count($notifications),
            'notifications' => $notifications,
        ]);
    }
}
