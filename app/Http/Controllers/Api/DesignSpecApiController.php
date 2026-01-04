<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\DesignSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DesignSpecApiController extends Controller
{
    private function rules(): array
    {
        return [
            'requirement_id' => 'required|exists:requirements,id',
            'artifact_type'  => 'required|in:UI,API,DB,Flow',
            'artifact_name'  => 'required|string|max:200',
            'reference_url'  => 'nullable|url|max:300',
            'rationale'      => 'nullable|string',
            'status'         => 'required|in:Draft,Review,Approved',
            'pic'            => 'nullable|string|max:120',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ];
    }

    /**
     * GET /api/projects/{project}/design-specs
     */
    public function index(Project $project)
    {
        // Kita load juga relasi 'requirement' supaya di JSON nanti ketahuan design ini untuk requirement mana
        $specs = $project->designSpecs()->with('requirement')->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Design specs retrieved successfully',
            'data' => $specs
        ], 200);
    }

    /**
     * POST /api/projects/{project}/design-specs
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

        // Create data via relationship
        $designSpec = $project->designSpecs()->create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Design specification created successfully',
            'data' => $designSpec
        ], 201);
    }

    /**
     * GET /api/projects/{project}/design-specs/{design_spec}
     */
    public function show(Project $project, DesignSpec $designSpec)
    {
        // Security check: pastikan design spec ini milik project tersebut
        if ($designSpec->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Design spec not found in this project'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Design spec detail retrieved',
            'data' => $designSpec->load('requirement') // load relasi requirement
        ], 200);
    }

    /**
     * PUT /api/projects/{project}/design-specs/{design_spec}
     */
    public function update(Request $request, Project $project, DesignSpec $designSpec)
    {
        if ($designSpec->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Design spec not found in this project'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $designSpec->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Design specification updated successfully',
            'data' => $designSpec
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/design-specs/{design_spec}
     */
    public function destroy(Project $project, DesignSpec $designSpec)
    {
        if ($designSpec->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Design spec not found in this project'], 404);
        }

        $designSpec->delete();

        return response()->json([
            'status' => true,
            'message' => 'Design specification deleted successfully'
        ], 200);
    }
}