<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * GET /api/dashboard-stats
     * Mengambil statistik project user untuk Home Screen
     */
    public function index(Request $request)
    {
        // 1. Ambil project milik user yang sedang login (via Token Sanctum)
        // Scope 'mine()' akan otomatis filter based on auth()->id()
        $projects = Project::mine()->get();

        // 2. Siapkan data statistik
        // Kita strukturkan biar JSON-nya rapi (User terpisah dari Stats)
        $data = [
            'user' => [
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
                // Tambahkan avatar jika ada, contoh:
                // 'avatar' => $request->user()->avatar_url, 
            ],
            'stats' => [
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
                
                // Opsional: Hitung Todo juga kalau mau lengkap
                'total_todo' => $projects
                     ->where('status', 'todo')
                     ->count(),
            ]
        ];

        // 3. Return JSON
        return response()->json([
            'status' => true,
            'message' => 'Dashboard statistics retrieved',
            'data' => $data
        ], 200);
    }
}