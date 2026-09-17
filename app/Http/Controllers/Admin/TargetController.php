<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\TargetClient;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function checkDeadlines()
    {
        $warnings = Target::where('is_active', true)->get()
            ->filter(function ($t) {
                return !$t->is_expired && $t->days_remaining <= 10;
            })
            ->map(fn($t) => [
                'id'               => $t->id,
                'title'            => $t->title,
                'days_remaining'   => $t->days_remaining,
                'remaining_target' => $t->remaining_target,
                'closed_count'     => $t->closed_count,
                'total_target'     => $t->total_target,
                'end_date'         => $t->end_date->format('M d, Y'),
            ])
            ->values();

        return response()->json(['targets' => $warnings]);
    }

    public function index()
    {
        $targets = Target::withCount('clients')->latest()->get();

        return view('admin.targets.index', compact('targets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'total_target'    => 'required|integer|min:1',
            'timeline_months' => 'required|integer|min:1|max:120',
            'start_date'      => 'required|date',
            'notes'           => 'nullable|string',
        ]);

        Target::create($validated);

        return back()->with('success', 'Target created successfully.');
    }

    public function update(Request $request, Target $target)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'total_target'    => 'required|integer|min:1',
            'timeline_months' => 'required|integer|min:1|max:120',
            'start_date'      => 'required|date',
            'notes'           => 'nullable|string',
            'is_active'       => 'boolean',
        ]);

        $target->update($validated);

        return back()->with('success', 'Target updated.');
    }

    public function destroy(Target $target)
    {
        $target->delete();

        return back()->with('success', 'Target deleted.');
    }

    public function show(Target $target)
    {
        $target->load('clients');

        return view('admin.targets.show', compact('target'));
    }

    public function addClient(Request $request, Target $target)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['target_id'] = $target->id;
        $client = TargetClient::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'client'  => $client,
                'stats'   => [
                    'closed_count'     => $target->fresh()->closed_count,
                    'remaining_target' => $target->fresh()->remaining_target,
                    'progress_percent' => $target->fresh()->progress_percent,
                    'days_remaining'   => $target->fresh()->days_remaining,
                ],
            ]);
        }

        return back()->with('success', 'Client added to target.');
    }

    public function removeClient(TargetClient $client)
    {
        $client->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Client removed.');
    }
}