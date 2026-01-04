<?php

namespace App\Http\Controllers;

use App\Models\{
    Project,
    ProjectActivity,
    ProjectFile
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlanningController extends Controller
{
    public function update(Request $request, Project $project)
    {
        // ================= AUTH =================
        abort_unless($project->user_id === auth()->id(), 403);

        // ================= VALIDATION =================
        $data = $request->validate([
            'planning_activity' => 'nullable|string|max:255',
            'planning_note'     => 'nullable|string',
            'files'             => 'nullable|array',
            'files.*'           => 'file|max:5120',
        ]);

        /* =====================================================
         | UPDATE PROJECT (ACTIVITY & NOTE)
         ===================================================== */
        if (!empty($data['planning_activity'])) {
            $project->activity = $data['planning_activity'];
        }

        if (array_key_exists('planning_note', $data)) {
            $project->planning_note = $data['planning_note'];
        }

        $project->save();

        /* =====================================================
         | LOG TIMELINE ACTIVITY
         ===================================================== */
        if (!empty($data['planning_activity'])) {
            ProjectActivity::create([
                'project_id'  => $project->id,
                'phase'       => 'planning',
                'action'      => 'update',
                'title'       => $data['planning_activity'],
                'description' => $data['planning_note'] ?? null,
                'occurred_at' => now(),
            ]);
        }

        /* =====================================================
         | UPLOAD FILES
         ===================================================== */
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('planning', 'public');

                ProjectFile::create([
                    'project_id'    => $project->id,
                    'phase'         => 'planning',
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'size'          => $file->getSize(),
                ]);
            }
        }

        return back()->with('ok', 'Planning berhasil diperbarui.');
    }

    /* =====================================================
     | DELETE FILE
     ===================================================== */
    public function destroyFile(ProjectFile $file)
    {
        abort_unless($file->project->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('ok', 'File berhasil dihapus.');
    }
}
