<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Task $task)
    {
        $request->validate(['content' => 'required|string|max:5000']);

        $userId   = Auth::id();
        /** @var User $authUser */
        $authUser = Auth::user();

        $comment = $task->comments()->create([
            'user_id' => $userId,
            'content' => $request->content,
        ]);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action'  => 'commented',
        ]);

        $task->touchActivity();
        $comment->load('user');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id'         => $comment->id,
                    'content'    => e($comment->content),
                    'created_at' => $comment->created_at->diffForHumans(),
                    'is_voice'   => false,
                    'voice_url'  => null,
                    'user'       => [
                        'name'       => $comment->user->name,
                        'initials'   => $comment->user->initials,
                        'avatar_url' => $comment->user->avatar_url,
                    ],
                    'can_edit'   => $userId === $comment->user_id || $authUser->isAdmin(),
                ],
            ]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function storeVoice(Request $request, Task $task)
    {
        $request->validate(['audio' => 'required|file|mimes:webm,ogg,mp4,wav|max:20480']);

        $userId   = Auth::id();
        /** @var User $authUser */
        $authUser = Auth::user();

        $path    = $request->file('audio')->store('voice-comments', 'public');
        $comment = $task->comments()->create([
            'user_id'    => $userId,
            'content'    => '[Voice message]',
            'voice_path' => $path,
            'is_voice'   => true,
        ]);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action'  => 'commented',
        ]);

        $task->touchActivity();
        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => [
                'id'         => $comment->id,
                'content'    => '[Voice message]',
                'created_at' => $comment->created_at->diffForHumans(),
                'is_voice'   => true,
                'voice_url'  => $comment->voice_url,
                'user'       => [
                    'name'       => $comment->user->name,
                    'initials'   => $comment->user->initials,
                    'avatar_url' => $comment->user->avatar_url,
                ],
                'can_edit'   => false,
            ],
        ]);
    }

    public function update(Request $request, TaskComment $comment)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (Auth::id() !== $comment->user_id && !$authUser->isAdmin()) {
            abort(403);
        }

        $request->validate(['content' => 'required|string|max:5000']);
        $comment->update(['content' => $request->content, 'is_edited' => true]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'content' => e($comment->content)]);
        }

        return back()->with('success', 'Comment updated.');
    }

    public function destroy(TaskComment $comment)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        if (Auth::id() !== $comment->user_id && !$authUser->isAdmin()) {
            abort(403);
        }

        if ($comment->voice_path) {
            Storage::disk('public')->delete($comment->voice_path);
        }

        $comment->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comment deleted.');
    }
}