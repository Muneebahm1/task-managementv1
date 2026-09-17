<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $statuses = TaskStatus::withCount('tasks')->orderBy('order')->get();
        return view('admin.statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'color'      => 'required|string|max:20',
            'category'   => 'required|in:backlog,active,done',
            'is_default' => 'boolean',
            'is_closed'  => 'boolean',
        ]);

        $validated['slug']       = Str::slug($validated['name'] . '-' . time());
        $validated['order']      = TaskStatus::max('order') + 1;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_closed']  = $request->boolean('is_closed');

        if ($validated['is_default']) {
            TaskStatus::where('is_default', true)->update(['is_default' => false]);
        }

        $status = TaskStatus::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $status]);
        }

        return redirect()->route('admin.statuses.index')->with('success', 'Status created.');
    }

    public function update(Request $request, TaskStatus $status)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'color'      => 'required|string|max:20',
            'category'   => 'required|in:backlog,active,done',
            'is_default' => 'boolean',
            'is_closed'  => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_closed']  = $request->boolean('is_closed');

        if ($validated['is_default']) {
            TaskStatus::where('is_default', true)->where('id', '!=', $status->id)->update(['is_default' => false]);
        }

        $status->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.statuses.index')->with('success', 'Status updated.');
    }

    public function destroy(TaskStatus $status)
    {
        if ($status->tasks()->count() > 0) {
            return back()->with('error', 'Cannot delete status with existing tasks. Reassign tasks first.');
        }
        $status->delete();
        return redirect()->route('admin.statuses.index')->with('success', 'Status deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'exists:task_statuses,id']);

        foreach ($request->order as $index => $id) {
            TaskStatus::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
