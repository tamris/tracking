<?php

namespace App\Http\Controllers;

use App\Models\{Project, TestCase};
use Illuminate\Http\Request;

class TestCaseController extends Controller
{
    /**
     * Simpan test case baru (fase Testing).
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'requirement_id'  => 'nullable|exists:requirements,id',
            'design_spec_id'  => 'nullable|exists:design_specs,id',
            'title'           => 'required|string|max:200',
            'scenario'        => 'nullable|string',
            'expected_result' => 'nullable|string',
            'tester'          => 'nullable|string|max:100',
            'status'          => 'required|in:Planned,In Progress,Passed,Failed',
            'start_at'        => 'nullable|date',
            'end_at'          => 'nullable|date|after_or_equal:start_at',
        ]);

        // 🔐 pastikan project_id dari route
        $data['project_id'] = $project->id;

        $project->testCases()->create($data);

        return redirect()
            ->route('projects.sdlc', [
                'project' => $project->id,
                'phase'   => 'testing',
            ])
            ->with('ok', 'Test case berhasil ditambahkan.');
    }

    /**
     * Update test case.
     */
    public function update(Request $request, TestCase $test_case)
    {
        $data = $request->validate([
            'requirement_id'  => 'nullable|exists:requirements,id',
            'design_spec_id'  => 'nullable|exists:design_specs,id',
            'title'           => 'required|string|max:200',
            'scenario'        => 'nullable|string',
            'expected_result' => 'nullable|string',
            'tester'          => 'nullable|string|max:100',
            'status'          => 'required|in:Planned,In Progress,Passed,Failed',
            'start_at'        => 'nullable|date',
            'end_at'          => 'nullable|date|after_or_equal:start_at',
        ]);

        $test_case->update($data);

        return redirect()
            ->route('projects.sdlc', [
                'project' => $test_case->project_id,
                'phase'   => 'testing',
            ])
            ->with('ok', 'Test case berhasil diperbarui.');
    }

    /**
     * Hapus test case.
     */
    public function destroy(TestCase $test_case)
    {
        $projectId = $test_case->project_id;

        $test_case->delete();

        return redirect()
            ->route('projects.sdlc', [
                'project' => $projectId,
                'phase'   => 'testing',
            ])
            ->with('ok', 'Test case berhasil dihapus.');
    }
}
