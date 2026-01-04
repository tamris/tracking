<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Development;
use App\Models\DesignSpec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DevelopmentApiController extends Controller
{
    private function rules(): array
    {
        return [
            'design_spec_id' => 'required|exists:design_specs,id',
            'pic'            => 'nullable|string|max:120',
            'status'         => 'required|in:In Progress,Review,Done',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ];
    }

    /**
     * GET /api/projects/{project}/developments
     * List semua task development di project ini
     */
    public function index(Project $project)
    {
        // Ambil data development, load juga design spec & requirement-nya biar lengkap
        $developments = $project->developments()
            ->with(['designSpec.requirement']) 
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Development tasks retrieved successfully',
            'data' => $developments
        ], 200);
    }

    /**
     * POST /api/projects/{project}/developments
     * Buat task development baru
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

        // VALIDASI KEAMANAN:
        // Pastikan Design Spec yang dipilih beneran milik project ini (via Requirement)
        // Kita cek relasi: DesignSpec -> Requirement -> Project
        $designSpec = DesignSpec::with('requirement')->find($request->design_spec_id);
        
        // Jika requirement-nya punya project_id yang beda dengan project saat ini, tolak.
        if (!$designSpec || $designSpec->requirement->project_id !== $project->id) {
             return response()->json([
                'status' => false,
                'message' => 'Design Spec ID does not belong to this project.'
            ], 400);
        }

        // Create Data
        $development = $project->developments()->create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Development task created successfully',
            'data' => $development->load('designSpec.requirement')
        ], 201);
    }

    /**
     * GET /api/projects/{project}/developments/{development}
     */
    public function show(Project $project, Development $development)
    {
        if ($development->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Development task not found in this project'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Development task detail retrieved',
            'data' => $development->load('designSpec.requirement')
        ], 200);
    }

    /**
     * PUT /api/projects/{project}/developments/{development}
     */
    public function update(Request $request, Project $project, Development $development)
    {
        if ($development->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Development task not found in this project'], 404);
        }

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Jika user mau ganti design_spec_id, validasi lagi kepemilikannya
        if ($request->has('design_spec_id') && $request->design_spec_id != $development->design_spec_id) {
            $newDesignSpec = DesignSpec::with('requirement')->find($request->design_spec_id);
            if (!$newDesignSpec || $newDesignSpec->requirement->project_id !== $project->id) {
                 return response()->json([
                    'status' => false,
                    'message' => 'New Design Spec ID does not belong to this project.'
                ], 400);
            }
        }

        $development->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Development task updated successfully',
            'data' => $development->fresh()->load('designSpec.requirement')
        ], 200);
    }

    /**
     * DELETE /api/projects/{project}/developments/{development}
     */
    public function destroy(Project $project, Development $development)
    {
        if ($development->project_id !== $project->id) {
            return response()->json(['status' => false, 'message' => 'Development task not found in this project'], 404);
        }

        $development->delete();

        return response()->json([
            'status' => true,
            'message' => 'Development task deleted successfully'
        ], 200);
    }
}