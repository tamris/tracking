<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PlanningApiController extends Controller
{
    /**
     * GET /api/projects/{project}/planning
     * Ambil data planning saat ini + file yang sudah diupload
     */
    public function index(Project $project)
    {
        // Cek Authorization (Opsional, jika middleware auth sudah handle)
        if ($project->user_id !== auth()->id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Ambil file khusus fase planning
        $files = $project->files()->where('phase', 'planning')->get()->map(function($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'size' => $file->size,
                'url'  => url('storage/' . $file->path), // Generate URL lengkap
                'created_at' => $file->created_at
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Planning data retrieved',
            'data' => [
                'planning_activity' => $project->activity,      // Current activity status
                'planning_note'     => $project->planning_note, // Catatan planning
                'files'             => $files
            ]
        ], 200);
    }

    /**
     * POST /api/projects/{project}/planning
     * Update planning note & Upload file
     */
    public function update(Request $request, Project $project)
    {
        // 1. Authorization Check
        if ($project->user_id !== auth()->id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // 2. Validation
        $validator = Validator::make($request->all(), [
            'planning_activity' => 'nullable|string|max:255',
            'planning_note'     => 'nullable|string',
            'files'             => 'nullable|array',
            'files.*'           => 'file|max:5120', // Max 5MB per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // 3. Update Project Data
        if (!empty($data['planning_activity'])) {
            $project->activity = $data['planning_activity'];
        }
        
        // Kita cek pake array_key_exists karena user mungkin mengirim string kosong untuk menghapus note
        if (array_key_exists('planning_note', $data)) {
            $project->planning_note = $data['planning_note'];
        }
        
        $project->save();

        // 4. Log Timeline Activity (Hanya jika activity berubah/diisi)
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

        // 5. Handle File Uploads
        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('planning', 'public');

                $newFile = ProjectFile::create([
                    'project_id'    => $project->id,
                    'phase'         => 'planning',
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'size'          => $file->getSize(),
                ]);
                
                $uploadedFiles[] = [
                    'id' => $newFile->id,
                    'name' => $newFile->original_name,
                    'url' => url('storage/' . $path)
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Planning updated successfully',
            'data' => [
                'planning_activity' => $project->activity,
                'planning_note'     => $project->planning_note,
                'uploaded_files_count' => count($uploadedFiles),
                'new_files' => $uploadedFiles
            ]
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/planning/files/{file}
     * Hapus file attachment
     */
    public function destroyFile(Project $project, ProjectFile $file)
    {
        // Security Check: Pastikan file milik project ini & User owner project
        if ($file->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'File not found in this project'], 404);
        }

        if ($project->user_id !== auth()->id()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Hapus fisik file
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        // Hapus record DB
        $file->delete();

        return response()->json([
            'status' => true,
            'message' => 'File deleted successfully'
        ], 200);
    }
}