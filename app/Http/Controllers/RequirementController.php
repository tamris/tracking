<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Requirement;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
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

    private function back(Project $project)
    {
        return redirect()->route('projects.sdlc', [
            'project' => $project->id,
            'phase'   => 'requirement',
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $project->requirements()->create(
            $request->validate($this->rules())
        );

        return $this->back($project)->with('ok', 'Requirement ditambahkan');
    }

    public function update(Request $request, Project $project, Requirement $requirement)
    {
        abort_if($requirement->project_id !== $project->id, 404);

        $requirement->update(
            $request->validate($this->rules())
        );

        return $this->back($project)->with('ok', 'Requirement diperbarui');
    }

    public function destroy(Project $project, Requirement $requirement)
    {
        abort_if($requirement->project_id !== $project->id, 404);

        $requirement->delete();

        return $this->back($project)->with('ok', 'Requirement dihapus');
    }
}
