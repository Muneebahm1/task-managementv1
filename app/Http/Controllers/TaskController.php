<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskStatus;
use App\Models\TaskView;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user     = Auth::user();
        $statuses = TaskStatus::orderBy('order')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();
        $userId   = $user->id;

        // Base query — employees now see ALL tasks
        $query = Task::with(['status', 'assignee', 'reporter', 'approvedBy',
            'userView' => fn($q) => $q->where('user_id', $userId),
        ]);

        if ($user->isClient()) {
            $query->where('reporter_id', $userId);
        }
        // Admin & Employee see everything (no extra filter)

        // Filters
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('task_key', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status'))   $query->where('status_id', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('assignee') && !$user->isClient()) {
            $query->where('assignee_id', $request->assignee);
        }

        // Split active vs completed
        $closedIds = TaskStatus::where('is_closed', true)->pluck('id');

        $activeTasks    = (clone $query)->whereNotIn('status_id', $closedIds)
            ->orderBy('created_at', 'desc')->paginate(15, ['*'], 'active_page')->withQueryString();

        $completedTasks = (clone $query)->whereIn('status_id', $closedIds)
            ->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'done_page')->withQueryString();

        // Unread count across all (non-paginated) active tasks
        $unreadCount = Task::whereNotIn('status_id', $closedIds)
            ->whereNotNull('last_activity_at')
            ->when($user->isClient(), fn($q) => $q->where('reporter_id', $userId))
            ->where(function ($q) use ($userId) {
                $q->whereDoesntHave('views', fn($v) => $v->where('user_id', $userId))
                  ->orWhereHas('views', fn($v) => $v->where('user_id', $userId)
                      ->whereColumn('task_views.last_viewed_at', '<', 'tasks.last_activity_at'));
            })
            ->count();

        return view('tasks.index', compact('activeTasks', 'completedTasks', 'statuses', 'users', 'unreadCount'));
    }

    public function board()
    {
        /** @var User $user */
        $user     = Auth::user();

        if ($user->isClient()) {
            return redirect()->route('tasks.index')->with('error', 'Board view is not available for clients.');
        }

        $statuses      = TaskStatus::orderBy('order')->get();
        $tasksByStatus = [];

        foreach ($statuses as $status) {
            $tasksByStatus[$status->id] = Task::with(['assignee', 'reporter', 'approvedBy'])
                ->where('status_id', $status->id)
                ->when($user->isEmployee(), fn($q) => $q->where('assignee_id', $user->id))
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return view('tasks.board', compact('statuses', 'tasksByStatus'));
    }

    public function create()
    {
        /** @var User $user */
        $user     = Auth::user();
        $statuses = TaskStatus::orderBy('order')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();

        if ($user->isClient()) {
            return view('tasks.create_client', compact('statuses'));
        }

        return view('tasks.create', compact('statuses', 'users'));
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isClient()) {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'type'        => 'required|in:task,bug,feature,story,epic,improvement',
                'priority'    => 'required|in:lowest,low,medium,high,highest',
            ]);

            $defaultStatus = TaskStatus::getDefault();
            $validated['task_key']    = Task::generateKey();
            $validated['reporter_id'] = $user->id;
            $validated['assignee_id'] = null;
            $validated['status_id']   = $defaultStatus->id;

        } else {
            $validated = $request->validate([
                'title'           => 'required|string|max:255',
                'description'     => 'nullable|string',
                'type'            => 'required|in:task,bug,feature,story,epic,improvement',
                'priority'        => 'required|in:lowest,low,medium,high,highest',
                'status_id'       => 'required|exists:task_statuses,id',
                'assignee_id'     => 'nullable|exists:users,id',
                'due_date'        => 'nullable|date',   // no min — allows back dates
                'story_points'    => 'nullable|integer|min:0',
                'estimated_hours' => 'nullable|integer|min:0',
                'labels'          => 'nullable|string|max:255',
                'task_date'       => 'nullable|date',   // admin backdate
            ]);

            $validated['task_key']    = Task::generateKey();
            $validated['reporter_id'] = $user->id;
        }

        $task = Task::create($validated);

        // Admin backdate: override created_at if task_date provided
        if ($user->isAdmin() && $request->filled('task_date')) {
            $task->timestamps = false;
            $task->created_at = Carbon::parse($request->task_date);
            $task->updated_at = Carbon::parse($request->task_date);
            $task->save();
            $task->timestamps = true;
        }

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action'  => 'created',
        ]);

        if ($request->hasFile('attachments')) {
            $this->storeAttachments($task, $request->file('attachments'));
        }

        // Mark as read immediately for creator
        $this->markAsRead($task, $user->id);

        return redirect()->route('tasks.show', $task)
            ->with('success', "Task {$task->task_key} created successfully.");
    }

    public function show(Task $task)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isClient() && $task->reporter_id !== $user->id) {
            abort(403, 'You can only view your own submitted tasks.');
        }

        if ($user->isEmployee() && $task->assignee_id !== $user->id && $task->reporter_id !== $user->id) {
            // Employees can now see all tasks, so allow
        }

        $task->load([
            'status', 'assignee', 'reporter', 'approvedBy',
            'comments'    => fn($q) => $q->with('user'),
            'attachments' => fn($q) => $q->with('user'),
            'activities'  => fn($q) => $q->with('user'),
        ]);
        $statuses = TaskStatus::orderBy('order')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();

        // Mark as read
        $this->markAsRead($task, $user->id);

        return view('tasks.show', compact('task', 'statuses', 'users'));
    }

    public function edit(Task $task)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admins can edit tasks.');
        }

        $statuses = TaskStatus::orderBy('order')->get();
        $users    = User::where('is_active', true)->orderBy('name')->get();
        return view('tasks.edit', compact('task', 'statuses', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isClient()) {
            abort(403, 'Clients cannot update tasks.');
        }

        if ($user->isEmployee()) {
            $validated = $request->validate(['status_id' => 'required|exists:task_statuses,id']);
            $oldStatus = $task->status->name;
            
            // If employee tries to set to done status, redirect to pending approval
            $newStatus = TaskStatus::find($validated['status_id']);
            if ($newStatus->is_closed) {
                $pendingApprovalStatus = TaskStatus::where('slug', 'pending-approval')->first();
                if ($pendingApprovalStatus) {
                    $validated['status_id'] = $pendingApprovalStatus->id;
                }
            }
            
            // Prevent employees from cancelling tasks (admin only)
            if ($newStatus->slug === 'cancelled') {
                if ($request->ajax()) {
                    return response()->json(['error' => 'Only admins can cancel tasks.'], 403);
                }
                return redirect()->route('tasks.show', $task)->with('error', 'Only admins can cancel tasks.');
            }
            
            $task->update($validated);
            $newStatusName = $task->fresh()->status->name;

            TaskActivity::create([
                'task_id'   => $task->id,
                'user_id'   => $user->id,
                'action'    => 'status_changed',
                'old_value' => $oldStatus,
                'new_value' => $newStatusName,
            ]);
            $task->touchActivity();

            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('tasks.show', $task)->with('success', 'Status updated.');
        }

        // Admin: full update
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:task,bug,feature,story,epic,improvement',
            'priority'        => 'required|in:lowest,low,medium,high,highest',
            'status_id'       => 'required|exists:task_statuses,id',
            'assignee_id'     => 'nullable|exists:users,id',
            'due_date'        => 'nullable|date',
            'story_points'    => 'nullable|integer|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'labels'          => 'nullable|string|max:255',
        ]);

        $oldStatusName = $task->status->name;
        $task->update($validated);
        $newStatusName = $task->fresh()->status->name;

        if ($oldStatusName !== $newStatusName) {
            TaskActivity::create([
                'task_id'   => $task->id,
                'user_id'   => $user->id,
                'action'    => 'status_changed',
                'old_value' => $oldStatusName,
                'new_value' => $newStatusName,
            ]);
        }

        if (isset($validated['assignee_id']) && $validated['assignee_id'] != $task->getOriginal('assignee_id')) {
            $newUserName = $validated['assignee_id'] ? User::find($validated['assignee_id'])?->name : 'Unassigned';
            TaskActivity::create([
                'task_id'   => $task->id,
                'user_id'   => $user->id,
                'action'    => 'assigned',
                'new_value' => $newUserName,
            ]);
        }

        $task->touchActivity();

        if ($request->hasFile('attachments')) {
            $this->storeAttachments($task, $request->file('attachments'));
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isClient()) {
            return response()->json(['error' => 'Clients cannot change task status.'], 403);
        }

        $validated = $request->validate(['status_id' => 'required|exists:task_statuses,id']);
        $oldStatus = $task->status->name;
        
        // If employee tries to set to done status, redirect to pending approval
        $newStatus = TaskStatus::find($validated['status_id']);
        if ($user->isEmployee() && $newStatus->is_closed) {
            $pendingApprovalStatus = TaskStatus::where('slug', 'pending-approval')->first();
            if ($pendingApprovalStatus) {
                $validated['status_id'] = $pendingApprovalStatus->id;
            }
        }
        
        // Prevent employees from cancelling tasks (admin only)
        if ($user->isEmployee() && $newStatus->slug === 'cancelled') {
            return response()->json(['error' => 'Only admins can cancel tasks.'], 403);
        }
        
        $task->update($validated);
        $newStatusName = $task->fresh()->status->name;

        TaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => $user->id,
            'action'    => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $newStatusName,
        ]);

        $task->touchActivity();

        return response()->json(['success' => true, 'status' => $task->fresh()->status]);
    }

    public function destroy(Task $task)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Only admins can delete tasks.');
        }
        $task->delete();
        return redirect()->route('tasks.index')->with('success', "Task {$task->task_key} deleted.");
    }

    public function assignSelf(Task $task)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isClient()) {
            return response()->json(['error' => 'Not allowed.'], 403);
        }

        if ($task->assignee_id !== null) {
            return response()->json(['error' => 'Task is already assigned.'], 422);
        }

        $task->update(['assignee_id' => $user->id]);

        TaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => $user->id,
            'action'    => 'assigned',
            'new_value' => $user->name,
        ]);

        $task->touchActivity();

        return response()->json([
            'success'      => true,
            'assignee_name' => $user->name,
            'assignee_initials' => $user->initials,
        ]);
    }

    public function markRead(Task $task)
    {
        $this->markAsRead($task, Auth::id());

        return response()->json(['success' => true]);
    }

    // ── Helpers ─────────────────────────────────────────────────

    private function markAsRead(Task $task, int $userId): void
    {
        TaskView::updateOrCreate(
            ['task_id' => $task->id, 'user_id' => $userId],
            ['last_viewed_at' => now()]
        );
    }

    private function storeAttachments(Task $task, array $files): void
    {
        $userId = Auth::id();
        foreach ($files as $file) {
            $path = $file->store('attachments', 'public');
            $task->attachments()->create([
                'user_id'       => $userId,
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
            ]);
            TaskActivity::create([
                'task_id'   => $task->id,
                'user_id'   => $userId,
                'action'    => 'attached',
                'new_value' => $file->getClientOriginalName(),
            ]);
        }
        $task->touchActivity();
    }

    public function approveTask(Request $request, Task $task)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Only admins can approve tasks.');
        }

        if (!$task->isPendingApproval()) {
            return redirect()->route('tasks.show', $task)->with('error', 'Task is not pending approval.');
        }

        $doneStatus = TaskStatus::where('slug', 'done')->first();
        if (!$doneStatus) {
            return redirect()->route('tasks.show', $task)->with('error', 'Done status not found.');
        }

        $oldStatus = $task->status->name;
        $task->update([
            'status_id' => $doneStatus->id,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'review_notes' => null,
        ]);
        $newStatus = $task->fresh()->status->name;

        TaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => $user->id,
            'action'    => 'approved',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
        ]);
        $task->touchActivity();

        return redirect()->route('tasks.show', $task)->with('success', 'Task approved successfully.');
    }

    public function sendForReview(Request $request, Task $task)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Only admins can send tasks for review.');
        }

        if (!$task->isPendingApproval()) {
            return redirect()->route('tasks.show', $task)->with('error', 'Task is not pending approval.');
        }

        $validated = $request->validate([
            'review_notes' => 'required|string|max:1000',
        ]);

        $inReviewStatus = TaskStatus::where('slug', 'in-review')->first();
        if (!$inReviewStatus) {
            return redirect()->route('tasks.show', $task)->with('error', 'In Review status not found.');
        }

        $oldStatus = $task->status->name;
        $task->update([
            'status_id' => $inReviewStatus->id,
            'approved_by' => null,
            'approved_at' => null,
            'review_notes' => $validated['review_notes'],
        ]);
        $newStatus = $task->fresh()->status->name;

        TaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => $user->id,
            'action'    => 'sent_for_review',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
        ]);
        $task->touchActivity();

        // Notify the assignee that the task was sent back for review
        if ($task->assignee) {
            // This is where you would add notification logic
            // For now, we'll create an activity that can be used as a notification
            TaskActivity::create([
                'task_id'   => $task->id,
                'user_id'   => $user->id,
                'action'    => 'review_notification',
                'new_value' => "Task sent back for review with notes: " . substr($validated['review_notes'], 0, 100),
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task sent back for review. Assignee has been notified.');
    }
}