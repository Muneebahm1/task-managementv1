<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Task $task)
    {
        $request->validate([
            'attachments'   => 'required|array',
            'attachments.*' => 'file|max:20480',
        ]);

        $userId   = Auth::id();
        $uploaded = [];

        foreach ($request->file('attachments') as $file) {
            $path       = $file->store('attachments', 'public');
            $attachment = $task->attachments()->create([
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

            $attachment->load('user');
            $uploaded[] = [
                'id'             => $attachment->id,
                'original_name'  => $attachment->original_name,
                'url'            => $attachment->url,
                'formatted_size' => $attachment->formatted_size,
                'icon'           => $attachment->icon,
                'is_image'       => $attachment->isImage(),
                'created_at'     => $attachment->created_at->diffForHumans(),
            ];
        }

        $task->touchActivity();

        return response()->json(['success' => true, 'attachments' => $uploaded]);
    }

    public function destroy(TaskAttachment $attachment)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (!$authUser->isAdmin() && Auth::id() !== $attachment->user_id) {
            abort(403);
        }

        TaskActivity::create([
            'task_id'   => $attachment->task_id,
            'user_id'   => Auth::id(),
            'action'    => 'deleted_attachment',
            'old_value' => $attachment->original_name,
        ]);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    public function download(TaskAttachment $attachment)
    {
        $path = storage_path('app/public/' . $attachment->file_path);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path, $attachment->original_name);
    }
}
