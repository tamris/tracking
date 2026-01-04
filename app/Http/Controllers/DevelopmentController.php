<?php

namespace App\Http\Controllers;

use App\Models\{Project, Development, DesignSpec};
use Illuminate\Http\Request;

class DevelopmentController extends Controller
{
    public function store(Request $r, Project $project)
    {
        $data = $r->validate([
            'design_spec_id' => 'required|exists:design_specs,id',
            'pic'            => 'nullable|string|max:120',
            'status'         => 'required|in:In Progress,Review,Done',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ]);

        abort_unless(
            DesignSpec::where('id',$data['design_spec_id'])
                ->where('project_id',$project->id)
                ->exists(),
            422
        );

        $project->developments()->create($data);

        return back()->with('ok','Development task ditambahkan.');
    }

    public function update(Request $r, Development $development)
    {
        $data = $r->validate([
            'design_spec_id' => 'required|exists:design_specs,id',
            'pic'            => 'nullable|string|max:120',
            'status'         => 'required|in:In Progress,Review,Done',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ]);

        $development->update($data);

        return redirect()
            ->route('projects.sdlc', [
                'project'=>$development->project_id,
                'phase'=>'development'
            ])
            ->with('ok','Development task diperbarui.');
    }

    public function destroy(Development $development)
    {
        $pid = $development->project_id;
        $development->delete();

        return redirect()
            ->route('projects.sdlc', [
                'project'=>$pid,
                'phase'=>'development'
            ])
            ->with('ok','Development task dihapus.');
    }
}
