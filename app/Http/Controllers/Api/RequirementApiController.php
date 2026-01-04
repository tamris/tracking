<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RequirementApiController extends Controller
{
    // Kita gunakan rules yang SAMA PERSIS dengan controller web kamu
    private function rules(): array
    {
        return [
            'title'      => 'required|string|max:200',
            'type'       => 'required|in:FR,NFR',
            'priority'   => 'required|in:Low,Medium,High',
            'status'     => 'required|in:Planned,In Progress,Done',
            'pic'        => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'acceptance_criteria' => 'nullable|string',
        ];
    }

    /**
     * GET /api/projects/{project}/requirements
     * Mengambil semua requirement dari project tertentu
     */
    public function index(Project $project)
    {
        $requirements = $project->requirements()->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'List requirements retrieved successfully',
            'data' => $requirements
        ], 200);
    }

    /**
     * POST /api/projects/{project}/requirements
     * Membuat requirement baru
     */
    public function store(Request $request, Project $project)
    {
        // Validasi manual agar bisa return JSON error custom jika gagal
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create data
        $requirement = $project->requirements()->create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Requirement created successfully',
            'data' => $requirement
        ], 201);
    }

    /**
     * GET /api/projects/{project}/requirements/{requirement}
     * Melihat detail satu requirement
     */
    public function show(Project $project, Requirement $requirement)
    {
        // Pastikan requirement ini milik project yang dimaksud
        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'status' => false,
                'message' => 'Requirement not found in this project'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Requirement detail retrieved',
            'data' => $requirement
        ], 200);
    }

    /**
     * PUT /api/projects/{project}/requirements/{requirement}
     * Update requirement
     */
    public function update(Request $request, Project $project, Requirement $requirement)
    {
        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'status' => false,
                'message' => 'Requirement not found in this project'
            ], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $requirement->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Requirement updated successfully',
            'data' => $requirement
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/requirements/{requirement}
     * Hapus requirement
     */
    public function destroy(Project $project, Requirement $requirement)
    {
        if ($requirement->project_id !== $project->id) {
            return response()->json([
                'status' => false,
                'message' => 'Requirement not found in this project'
            ], 404);
        }

        $requirement->delete();

        return response()->json([
            'status' => true,
            'message' => 'Requirement deleted successfully'
        ], 200);
    }
}