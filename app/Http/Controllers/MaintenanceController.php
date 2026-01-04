<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'status'    => 'required|in:Planned,In Progress,Resolved,Closed',
            'assignee'  => 'nullable|string|max:100',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
            'notes'     => 'nullable|string',
        ]);

        $project->maintenances()->create([
            ...$data,
            'progress_percentage' => Maintenance::progressFromStatus($data['status']),
            'opened_at' => $data['opened_at'] ?? now(),
            'closed_at' => in_array($data['status'], ['Resolved','Closed'])
                ? ($data['closed_at'] ?? now())
                : null,
        ]);

        return back()->with('ok', 'Maintenance berhasil ditambahkan.');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'status'    => 'required|in:Planned,In Progress,Resolved,Closed',
            'assignee'  => 'nullable|string|max:100',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
            'notes'     => 'nullable|string',
        ]);

        $maintenance->update([
            ...$data,
            'progress_percentage' => Maintenance::progressFromStatus($data['status']),
            'closed_at' => in_array($data['status'], ['Resolved','Closed'])
                ? ($data['closed_at'] ?? now())
                : null,
        ]);

        return back()->with('ok', 'Maintenance berhasil diperbarui.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();
        return back()->with('ok', 'Maintenance berhasil dihapus.');
    }
}
