<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Deployment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeploymentApiController extends Controller
{
    /**
     * Rules validasi standar
     */
    private function rules(): array
    {
        return [
            'environment' => 'required|string|max:100', // e.g. Staging, Production
            'version'     => 'nullable|string|max:100', // e.g. v1.0.2
            'status'      => 'required|in:Planned,In Progress,Success,Failed',
            'pic'         => 'nullable|string|max:100', // DevOps / Engineer name
            'start_at'    => 'nullable|date',
            'end_at'      => 'nullable|date|after_or_equal:start_at',
            'url'         => 'nullable|url|max:255',    // URL hasil deploy
            'notes'       => 'nullable|string',
        ];
    }

    /**
     * GET /api/projects/{project}/deployments
     * List history deployment
     */
    public function index(Project $project)
    {
        $deployments = $project->deployments()
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Deployment history retrieved successfully',
            'data' => $deployments
        ], 200);
    }

    /**
     * POST /api/projects/{project}/deployments
     * Tambah log deployment baru
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

        // Create data (otomatis project_id terisi via relasi)
        $deployment = $project->deployments()->create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Deployment log created successfully',
            'data' => $deployment
        ], 201);
    }

    /**
     * GET /api/projects/{project}/deployments/{deployment}
     */
    public function show(Project $project, Deployment $deployment)
    {
        // Security check: Pastikan deployment ini milik project tersebut
        if ($deployment->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Deployment record not found in this project'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Deployment detail retrieved',
            'data' => $deployment
        ], 200);
    }

    /**
     * PUT /api/projects/{project}/deployments/{deployment}
     */
    public function update(Request $request, Project $project, Deployment $deployment)
    {
        if ($deployment->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Deployment record not found in this project'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $deployment->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Deployment record updated successfully',
            'data' => $deployment->fresh()
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/deployments/{deployment}
     */
    public function destroy(Project $project, Deployment $deployment)
    {
        if ($deployment->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Deployment record not found in this project'], 404);
        }

        $deployment->delete();

        return response()->json([
            'status' => true,
            'message' => 'Deployment record deleted successfully'
        ], 200);
    }
}