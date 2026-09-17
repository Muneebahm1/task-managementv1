<?php

use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => false, 'register' => false]);

Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/board', [TaskController::class, 'board'])->name('tasks.board');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('/tasks/{task}/assign-self', [TaskController::class, 'assignSelf'])->name('tasks.assign-self');
    Route::patch('/tasks/{task}/mark-read', [TaskController::class, 'markRead'])->name('tasks.mark-read');
    Route::post('/tasks/{task}/approve', [TaskController::class, 'approveTask'])->name('tasks.approve');
    Route::post('/tasks/{task}/send-for-review', [TaskController::class, 'sendForReview'])->name('tasks.send-for-review');

    // Notifications
    Route::get('/notifications/deadlines', [NotificationController::class, 'deadlines'])->name('notifications.deadlines');

    // Comments
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/tasks/{task}/voice-comment', [CommentController::class, 'storeVoice'])->name('comments.store-voice');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachments
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        Route::get('statuses', [StatusController::class, 'index'])->name('statuses.index');
        Route::post('statuses', [StatusController::class, 'store'])->name('statuses.store');
        Route::put('statuses/{status}', [StatusController::class, 'update'])->name('statuses.update');
        Route::delete('statuses/{status}', [StatusController::class, 'destroy'])->name('statuses.destroy');
        Route::post('statuses/reorder', [StatusController::class, 'reorder'])->name('statuses.reorder');

        // Contacts
        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
        Route::put('contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

        // Targets
        Route::get('targets', [TargetController::class, 'index'])->name('targets.index');
        Route::get('targets/check-deadlines', [TargetController::class, 'checkDeadlines'])->name('targets.check-deadlines');
        Route::post('targets', [TargetController::class, 'store'])->name('targets.store');
        Route::put('targets/{target}', [TargetController::class, 'update'])->name('targets.update');
        Route::delete('targets/{target}', [TargetController::class, 'destroy'])->name('targets.destroy');
        Route::get('targets/{target}', [TargetController::class, 'show'])->name('targets.show');
        Route::post('targets/{target}/clients', [TargetController::class, 'addClient'])->name('targets.clients.add');
        Route::delete('target-clients/{client}', [TargetController::class, 'removeClient'])->name('targets.clients.remove');
    });
});