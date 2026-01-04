<?php

namespace App\Http\Controllers;

use App\Models\{Project, Deployment};
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    /**
     * Simpan deployment baru
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'environment' => 'required|string|max:100',
            'version'     => 'nullable|string|max:100',
            'status'      => 'required|in:Planned,In Progress,Success,Failed',
            'pic'         => 'nullable|string|max:100',
            'start_at'    => 'nullable|date',
            'end_at'      => 'nullable|date|after_or_equal:start_at',
            'url'         => 'nullable|url|max:255',
            'notes'       => 'nullable|string',
        ]);

        // 🔥 WAJIB – pastikan relasi project
        $data['project_id'] = $project->id;

        Deployment::create($data);

        return redirect()
            ->route('projects.sdlc', [
                'project' => $project->id,
                'phase'   => 'deployment'
            ])
            ->with('ok', 'Deployment berhasil ditambahkan.');
    }

    /**
     * Update deployment
     */
    public function update(Request $request, Deployment $deployment)
    {
        $data = $request->validate([
            'environment' => 'required|string|max:100',
            'version'     => 'nullable|string|max:100',
            'status'      => 'required|in:Planned,In Progress,Success,Failed',
            'pic'         => 'nullable|string|max:100',
            'start_at'    => 'nullable|date',
            'end_at'      => 'nullable|date|after_or_equal:start_at',
            'url'         => 'nullable|url|max:255',
            'notes'       => 'nullable|string',
        ]);

        $deployment->update($data);

        return redirect()
            ->route('projects.sdlc', [
                'project' => $deployment->project_id,
                'phase'   => 'deployment'
            ])
            ->with('ok', 'Deployment berhasil diperbarui.');
    }

    /**
     * Hapus deployment
     */
    public function destroy(Deployment $deployment)
    {
        $projectId = $deployment->project_id;
        $deployment->delete();

        return redirect()
            ->route('projects.sdlc', [
                'project' => $projectId,
                'phase'   => 'deployment'
            ])
            ->with('ok', 'Deployment berhasil dihapus.');
    }
}
