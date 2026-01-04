<?php

namespace App\Http\Controllers;

use App\Models\{
    Project,
    ProjectActivity,
    ProjectFile
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /* =========================================================
     | LIST PROJECT
     ========================================================= */
    public function index()
    {
        $projects = Project::mine()->latest('id')->get();
        return view('Feature.project', compact('projects'));
    }

    /* =========================================================
     | DETAIL → REDIRECT KE SDLC PLANNING
     ========================================================= */
    public function show(Project $project)
    {
        $this->authorizeOwner($project);

        return redirect()->route('projects.sdlc', [
            'project' => $project->id,
            'phase'   => 'planning',
        ]);
    }

    /* =========================================================
     | STORE PROJECT
     ========================================================= */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id']  = $request->user()->id;
        $data['progress'] = $data['progress'] ?? 0;

        if ($request->hasFile('contract_file')) {
            $data['contract_file'] = $this->storePublicFileKeepName(
                $request->file('contract_file'),
                'contracts'
            );
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storePublicFileKeepName(
                $request->file('cover_image'),
                'covers'
            );
        }

        Project::create($data);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project berhasil ditambahkan.');
    }

    /* =========================================================
     | UPDATE PROJECT
     ========================================================= */
    public function update(Request $request, Project $project)
    {
        $this->authorizeOwner($project);

        $data = $this->validated($request);
        $data['progress'] = $data['progress'] ?? 0;

        if ($request->hasFile('contract_file')) {
            $this->deletePublicIfExists($project->contract_file);
            $data['contract_file'] = $this->storePublicFileKeepName(
                $request->file('contract_file'),
                'contracts'
            );
        }

        if ($request->hasFile('cover_image')) {
            $this->deletePublicIfExists($project->cover_image);
            $data['cover_image'] = $this->storePublicFileKeepName(
                $request->file('cover_image'),
                'covers'
            );
        }

        $project->update($data);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project berhasil diperbarui.');
    }

    /* =========================================================
     | DELETE PROJECT
     ========================================================= */
    public function destroy(Project $project)
    {
        $this->authorizeOwner($project);

        $this->deletePublicIfExists($project->contract_file);
        $this->deletePublicIfExists($project->cover_image);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project berhasil dihapus.');
    }

    /* =========================================================
     | SDLC PHASE VIEW (PLANNING → MAINTENANCE)
     ========================================================= */
    public function showPhase(Project $project, string $phase)
    {
        $this->authorizeOwner($project);

        $phases = [
            'planning'    => 'Planning',
            'requirement' => 'Requirement',
            'design'      => 'Design',
            'development' => 'Development', 
            'testing'     => 'Testing',
            'deployment'  => 'Deployment',
            'maintenance' => 'Maintenance',
        ];

        $key = Str::of($phase)->lower()->value();
        if (!array_key_exists($key, $phases)) {
            $key = 'planning';
        }

        // ===== LOAD RELASI SDLC =====
        $project->load([
            'requirements',
            'designSpecs',
            'developments',
            'testCases',
            'deployments',
            'maintenances',
        ]);

        // ===== DATA KHUSUS PLANNING =====
        $activities = collect();
        $planningFiles = collect();

        if ($key === 'planning') {
            $activities = ProjectActivity::where('project_id', $project->id)
                ->where('phase', 'planning')
                ->latest('occurred_at')
                ->get();

            $planningFiles = ProjectFile::where('project_id', $project->id)
                ->where('phase', 'planning')
                ->latest()
                ->get();
        }

        return view('Feature.project-detail', [
            'project'       => $project,
            'phase'         => $key,
            'phaseName'     => $phases[$key],
            'phases'        => $phases,
            'activities'    => $activities,
            'planningFiles' => $planningFiles,
        ]);
    }

    /* =========================================================
     | VALIDATION
     ========================================================= */
    protected function validated(Request $req): array
    {
        return $req->validate([
            'title'      => ['required','string','max:255'],
            'pic'        => ['nullable','string','max:255'],
            'status'     => ['required', Rule::in(['todo','in_progress','review','done'])],
            'start_date' => ['nullable','date'],
            'end_date'   => ['nullable','date','after_or_equal:start_date'],
            'progress'   => ['nullable','integer','min:0','max:100'],
            'activity'   => ['nullable','string','max:255'],

            'contract_file' => ['nullable','file','max:5120','mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'cover_image'   => ['nullable','image','max:4096','mimes:jpg,jpeg,png,webp'],
        ]);
    }

    /* =========================================================
     | AUTH
     ========================================================= */
    protected function authorizeOwner(Project $project): void
    {
        abort_unless(
            $project->user_id === auth()->id(),
            403,
            'Tidak diizinkan mengakses project ini.'
        );
    }

    /* =========================================================
     | FILE HELPERS
     ========================================================= */
    private function deletePublicIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function storePublicFileKeepName($file, string $folder): string
    {
        $original = $file->getClientOriginalName();
        $name = pathinfo($original, PATHINFO_FILENAME);
        $ext  = $file->getClientOriginalExtension();

        $name = preg_replace('/[^\pL\pN\-\_\s]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));
        $name = $name !== '' ? $name : 'file';

        $candidate = $name . '.' . $ext;
        $i = 1;

        while (Storage::disk('public')->exists($folder.'/'.$candidate)) {
            $candidate = $name." ($i).".$ext;
            $i++;
        }

        return $file->storeAs($folder, $candidate, 'public');
    }
}
