<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestCase;
use App\Models\Deployment;
use App\Models\Maintenance;

class ProgressController extends Controller
{
    public function updateTestCase(Request $request, TestCase $testCase)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        $testCase->update([
            'progress_percentage' => $request->progress_percentage,
            'status' => $request->progress_percentage == 100
                        ? 'Completed'
                        : 'In Progress',
        ]);

        return back()->with('success', 'Progress Testing berhasil diupdate');
    }

    public function updateDeployment(Request $request, Deployment $deployment)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        $deployment->update([
            'progress_percentage' => $request->progress_percentage,
            'status' => $request->progress_percentage == 100
                        ? 'Completed'
                        : 'In Progress',
        ]);

        return back()->with('success', 'Progress Deployment berhasil diupdate');
    }

    public function updateMaintenance(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);

        $maintenance->update([
            'progress_percentage' => $request->progress_percentage,
            'status' => $request->progress_percentage == 100
                        ? 'Completed'
                        : 'In Progress',
        ]);

        return back()->with('success', 'Progress Maintenance berhasil diupdate');
    }
}
