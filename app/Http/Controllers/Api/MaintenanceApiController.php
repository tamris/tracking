<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaintenanceApiController extends Controller
{
    private function rules(): array
    {
        return [
            'title'     => 'required|string|max:255',
            'status'    => 'required|in:Planned,In Progress,Resolved,Closed',
            'assignee'  => 'nullable|string|max:100',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
            'notes'     => 'nullable|string',
        ];
    }

    /**
     * GET /api/projects/{project}/maintenances
     */
    public function index(Project $project)
    {
        $maintenances = $project->maintenances()
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Maintenance tasks retrieved successfully',
            'data' => $maintenances
        ], 200);
    }

    /**
     * POST /api/projects/{project}/maintenances
     */
    public function store(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // --- LOGIC OTOMATIS (Sama seperti Web Controller) ---
        // 1. Hitung progress persen berdasarkan status (Pastikan fungsi ini ada di Model)
        $progress = Maintenance::progressFromStatus($data['status']);

        // 2. Logic Tanggal
        $openedAt = $data['opened_at'] ?? now();
        $closedAt = in_array($data['status'], ['Resolved', 'Closed'])
            ? ($data['closed_at'] ?? now())
            : null;

        $maintenance = $project->maintenances()->create([
            'title'               => $data['title'],
            'status'              => $data['status'],
            'assignee'            => $data['assignee'],
            'notes'               => $data['notes'],
            'opened_at'           => $openedAt,
            'closed_at'           => $closedAt,
            'progress_percentage' => $progress, // Field ini otomatis terisi
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Maintenance task created successfully',
            'data' => $maintenance
        ], 201);
    }

    /**
     * GET /api/projects/{project}/maintenances/{maintenance}
     */
    public function show(Project $project, Maintenance $maintenance)
    {
        if ($maintenance->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Maintenance task not found in this project'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Maintenance detail retrieved',
            'data' => $maintenance
        ], 200);
    }

    /**
     * PUT /api/projects/{project}/maintenances/{maintenance}
     */
    public function update(Request $request, Project $project, Maintenance $maintenance)
    {
        if ($maintenance->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Maintenance task not found in this project'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // --- LOGIC UPDATE OTOMATIS ---
        $progress = Maintenance::progressFromStatus($data['status']);
        
        $closedAt = in_array($data['status'], ['Resolved', 'Closed'])
            ? ($data['closed_at'] ?? now())
            : null;

        $maintenance->update([
            'title'               => $data['title'],
            'status'              => $data['status'],
            'assignee'            => $data['assignee'],
            'notes'               => $data['notes'],
            'opened_at'           => $data['opened_at'] ?? $maintenance->opened_at, // Pertahankan yg lama jika null
            'closed_at'           => $closedAt,
            'progress_percentage' => $progress,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Maintenance task updated successfully',
            'data' => $maintenance->fresh()
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/maintenances/{maintenance}
     */
    public function destroy(Project $project, Maintenance $maintenance)
    {
        if ($maintenance->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Maintenance task not found in this project'], 404);
        }

        $maintenance->delete();

        return response()->json([
            'status' => true,
            'message' => 'Maintenance task deleted successfully'
        ], 200);
    }
}