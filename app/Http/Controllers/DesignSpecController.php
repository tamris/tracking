<?php

namespace App\Http\Controllers;

use App\Models\{Project, DesignSpec};
use Illuminate\Http\Request;

class DesignSpecController extends Controller
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

    public function store(Request $request, Project $project)
    {
        $project->designSpecs()->create(
            $request->validate($this->rules())
        );

        return back()->with('ok', 'Design Specification berhasil ditambahkan');
    }

    public function update(Request $request, DesignSpec $design_spec)
    {
        $design_spec->update(
            $request->validate($this->rules())
        );

        return back()->with('ok', 'Design Specification diperbarui');
    }

    public function destroy(DesignSpec $design_spec)
    {
        $design_spec->delete();

        return back()->with('ok', 'Design Specification dihapus');
    }
}
