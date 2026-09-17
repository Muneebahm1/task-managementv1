<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Statuses
        $statuses = [
            ['name' => 'Backlog',     'slug' => 'backlog',     'color' => '#6B778C', 'category' => 'backlog', 'order' => 0, 'is_default' => true,  'is_closed' => false],
            ['name' => 'To Do',       'slug' => 'to-do',       'color' => '#0052CC', 'category' => 'backlog', 'order' => 1, 'is_default' => false, 'is_closed' => false],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color' => '#FF8C00', 'category' => 'active',  'order' => 2, 'is_default' => false, 'is_closed' => false],
            ['name' => 'In Review',   'slug' => 'in-review',   'color' => '#6554C0', 'category' => 'active',  'order' => 3, 'is_default' => false, 'is_closed' => false],
            ['name' => 'Testing',     'slug' => 'testing',     'color' => '#00B8D9', 'category' => 'active',  'order' => 4, 'is_default' => false, 'is_closed' => false],
            ['name' => 'Done',        'slug' => 'done',        'color' => '#36B37E', 'category' => 'done',    'order' => 5, 'is_default' => false, 'is_closed' => true],
            ['name' => 'Cancelled',   'slug' => 'cancelled',   'color' => '#FF5630', 'category' => 'done',    'order' => 6, 'is_default' => false, 'is_closed' => true],
        ];

        foreach ($statuses as $s) {
            TaskStatus::create($s);
        }

        // Admin user
        $admin = User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@taskflow.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'job_title' => 'Project Manager',
            'is_active' => true,
        ]);

        // Employees
        $employeeData = [
            ['name' => 'Alice Johnson', 'email' => 'alice@taskflow.com', 'job_title' => 'Frontend Developer'],
            ['name' => 'Bob Smith',     'email' => 'bob@taskflow.com',   'job_title' => 'Backend Developer'],
            ['name' => 'Carol White',   'email' => 'carol@taskflow.com', 'job_title' => 'QA Engineer'],
            ['name' => 'David Brown',   'email' => 'david@taskflow.com', 'job_title' => 'DevOps Engineer'],
        ];

        $empUsers = [];
        foreach ($employeeData as $e) {
            $empUsers[] = User::create(array_merge($e, [
                'password'  => Hash::make('password'),
                'role'      => 'employee',
                'is_active' => true,
            ]));
        }

        // Demo employee
        User::create([
            'name'      => 'Employee User',
            'email'     => 'employee@taskflow.com',
            'password'  => Hash::make('password'),
            'role'      => 'employee',
            'job_title' => 'Software Developer',
            'is_active' => true,
        ]);

        // Demo client
        User::create([
            'name'      => 'Client User',
            'email'     => 'client@taskflow.com',
            'password'  => Hash::make('password'),
            'role'      => 'client',
            'job_title' => 'Client',
            'is_active' => true,
        ]);

        $allStatuses = TaskStatus::all();
        $allUsers    = User::all();

        $sampleTasks = [
            ['title' => 'Set up CI/CD pipeline',               'type' => 'task',        'priority' => 'high',    'status' => 'In Progress', 'description' => 'Configure GitHub Actions for automated build, test, and deployment pipeline.'],
            ['title' => 'Fix login page redirect bug',         'type' => 'bug',         'priority' => 'highest', 'status' => 'To Do',       'description' => 'After login, users are sometimes redirected to 404 instead of dashboard.'],
            ['title' => 'Design new dashboard UI',             'type' => 'feature',     'priority' => 'medium',  'status' => 'In Review',   'description' => 'Create modern, data-rich dashboard with charts and quick stats.'],
            ['title' => 'Implement JWT authentication',        'type' => 'story',       'priority' => 'high',    'status' => 'Done',        'description' => 'Replace session-based auth with JWT tokens for the API.'],
            ['title' => 'Add multi-language support',          'type' => 'improvement', 'priority' => 'low',     'status' => 'Backlog',     'description' => 'Support English, Arabic, and French languages.'],
            ['title' => 'Optimize database queries',           'type' => 'improvement', 'priority' => 'medium',  'status' => 'In Progress', 'description' => 'Reduce N+1 queries and add proper indexes on tasks table.'],
            ['title' => 'Task Management Epic',                'type' => 'epic',        'priority' => 'highest', 'status' => 'In Progress', 'description' => 'Complete task management system inspired by Jira.'],
            ['title' => 'Write API documentation',             'type' => 'task',        'priority' => 'medium',  'status' => 'Backlog',     'description' => 'Document all REST API endpoints using Swagger/OpenAPI.'],
            ['title' => 'Broken file upload for attachments',  'type' => 'bug',         'priority' => 'high',    'status' => 'Testing',     'description' => 'File uploads fail silently when file exceeds 5MB.'],
            ['title' => 'Kanban board drag and drop',          'type' => 'feature',     'priority' => 'high',    'status' => 'Done',        'description' => 'Implement SortableJS drag and drop for Kanban board.'],
            ['title' => 'Email notifications for assignments', 'type' => 'feature',     'priority' => 'low',     'status' => 'Backlog',     'description' => 'Send email when a task is assigned to a user.'],
            ['title' => 'User avatar upload',                  'type' => 'improvement', 'priority' => 'low',     'status' => 'Done',        'description' => 'Allow users to upload profile avatars.'],
        ];

        $keyCounter = 1;
        foreach ($sampleTasks as $t) {
            $status   = $allStatuses->firstWhere('name', $t['status']);
            $assignee = $allUsers->random();
            $dueDate  = (rand(0, 1)) ? now()->addDays(rand(-5, 30))->format('Y-m-d') : null;

            $task = Task::create([
                'task_key'        => 'TASK-' . str_pad($keyCounter++, 4, '0', STR_PAD_LEFT),
                'title'           => $t['title'],
                'description'     => $t['description'],
                'type'            => $t['type'],
                'priority'        => $t['priority'],
                'status_id'       => $status->id,
                'reporter_id'     => $admin->id,
                'assignee_id'     => $assignee->id,
                'due_date'        => $dueDate,
                'story_points'    => rand(1, 13),
                'estimated_hours' => rand(1, 40),
            ]);

            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => $admin->id,
                'action'  => 'created',
            ]);

            if (rand(0, 1)) {
                $commenter = $allUsers->random();
                TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => $commenter->id,
                    'content' => 'Working on this now. Should be done by end of sprint.',
                ]);
                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => $commenter->id,
                    'action'  => 'commented',
                ]);
            }
        }
    }
}
