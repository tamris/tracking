<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TestCase;
use App\Models\Requirement;
use App\Models\DesignSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestCaseApiController extends Controller
{
    private function rules(): array
    {
        return [
            'requirement_id'  => 'nullable|exists:requirements,id',
            'design_spec_id'  => 'nullable|exists:design_specs,id',
            'title'           => 'required|string|max:200',
            'scenario'        => 'nullable|string',
            'expected_result' => 'nullable|string',
            'tester'          => 'nullable|string|max:100',
            'status'          => 'required|in:Planned,In Progress,Passed,Failed',
            'start_at'        => 'nullable|date',
            'end_at'          => 'nullable|date|after_or_equal:start_at',
        ];
    }

    /**
     * GET /api/projects/{project}/test-cases
     */
    public function index(Project $project)
    {
        $testCases = $project->testCases()
            ->with(['requirement', 'designSpec']) // Load relasi biar namanya muncul
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Test cases retrieved successfully',
            'data' => $testCases
        ], 200);
    }

    /**
     * POST /api/projects/{project}/test-cases
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

        // --- VALIDASI LOGIC (KEAMANAN) ---
        
        // 1. Jika user mengisi requirement_id, pastikan milik project ini
        if ($request->filled('requirement_id')) {
            $req = Requirement::find($request->requirement_id);
            if (!$req || $req->project_id !== $project->id) {
                return response()->json(['status' => false, 'message' => 'Requirement ID does not belong to this project.'], 400);
            }
        }

        // 2. Jika user mengisi design_spec_id, pastikan milik project ini (via requirement atau direct check kalau ada relation)
        if ($request->filled('design_spec_id')) {
            // Kita cek design spec -> requirement -> project
            $ds = DesignSpec::with('requirement')->find($request->design_spec_id);
            if (!$ds || optional($ds->requirement)->project_id !== $project->id) {
                return response()->json(['status' => false, 'message' => 'Design Spec ID does not belong to this project.'], 400);
            }
        }

        // Create data
        $testCase = $project->testCases()->create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Test case created successfully',
            'data' => $testCase->load(['requirement', 'designSpec'])
        ], 201);
    }

    /**
     * GET /api/projects/{project}/test-cases/{test_case}
     */
    public function show(Project $project, TestCase $testCase)
    {
        if ($testCase->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Test case not found in this project'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Test case detail retrieved',
            'data' => $testCase->load(['requirement', 'designSpec'])
        ], 200);
    }

    /**
     * PUT /api/projects/{project}/test-cases/{test_case}
     */
    public function update(Request $request, Project $project, TestCase $testCase)
    {
        if ($testCase->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Test case not found in this project'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        // Validasi ulang jika ID requirement/design berubah (sama seperti store)
        if ($request->filled('requirement_id') && $request->requirement_id != $testCase->requirement_id) {
             $req = Requirement::find($request->requirement_id);
             if (!$req || $req->project_id !== $project->id) return response()->json(['status' => false, 'message' => 'Requirement ID does not belong to this project'], 400);
        }
        
        if ($request->filled('design_spec_id') && $request->design_spec_id != $testCase->design_spec_id) {
             $ds = DesignSpec::with('requirement')->find($request->design_spec_id);
             if (!$ds || optional($ds->requirement)->project_id !== $project->id) return response()->json(['status' => false, 'message' => 'Design Spec ID does not belong to this project'], 400);
        }

        $testCase->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Test case updated successfully',
            'data' => $testCase->fresh()->load(['requirement', 'designSpec'])
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/test-cases/{test_case}
     */
    public function destroy(Project $project, TestCase $testCase)
    {
        if ($testCase->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Test case not found in this project'], 404);
        }

        $testCase->delete();

        return response()->json([
            'status' => true,
            'message' => 'Test case deleted successfully'
        ], 200);
    }
}