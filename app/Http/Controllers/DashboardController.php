<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil project milik user
        $projects = Project::mine()->get();

        $stats = [
            'user_name' => $request->user()->name,

            'total_project' => $projects->count(),

            'total_in_progress' => $projects
                ->where('status', 'in_progress')
                ->count(),

            'total_review' => $projects
                ->where('status', 'review')
                ->count(),

            'total_done' => $projects
                ->where('status', 'done')
                ->count(),
        ];

        return view('Auth.dashboard', compact('stats'));
    }
}
