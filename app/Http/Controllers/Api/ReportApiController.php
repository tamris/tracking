<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    /**
     * GET /api/reports
     * Params: start (Y-m-d), end (Y-m-d), export (csv)
     */
    public function index(Request $request)
    {
        // 1. Filter Rentang Tanggal
        // Kita pakai try-catch untuk jaga-jaga format tanggal salah
        try {
            $start = $request->filled('start') ? Carbon::parse($request->query('start'))->startOfDay() : null;
            $end   = $request->filled('end')   ? Carbon::parse($request->query('end'))->endOfDay()   : null;
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Invalid date format'], 400);
        }

        // 2. Query Project (Milik User)
        $query = Project::mine();
        
        if ($start) $query->whereDate('start_date', '>=', $start->toDateString());
        if ($end)   $query->whereDate('start_date', '<=', $end->toDateString());

        $projects = $query->orderBy('start_date')->get();

        // 3. Handle Export CSV (Langsung return download stream)
        if ($request->query('export') === 'csv') {
            $filename = 'projects-report-'.now()->format('Ymd_His').'.csv';
            return response()->streamDownload(function () use ($projects) {
                $out = fopen('php://output','w');
                // Header CSV
                fputcsv($out, ['Nama Proyek', 'PIC', 'Status', 'Mulai', 'Selesai', 'Overall Progress (%)', 'Activity Terakhir']);
                
                foreach ($projects as $p) {
                    fputcsv($out, [
                        $p->title,
                        $p->pic,
                        ucfirst($p->status), // Uppercase status
                        optional($p->start_date)?->format('Y-m-d'),
                        optional($p->end_date)?->format('Y-m-d'),
                        (int)($p->overall_progress ?? 0), // Pakai accessor overall_progress yg kita buat sebelumnya
                        $p->activity,
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        // 4. Kalkulasi Statistik (Summary)
        $total = $projects->count();
        $statusCounts = [
            'todo'        => $projects->where('status','todo')->count(),
            'in_progress' => $projects->where('status','in_progress')->count(),
            'review'      => $projects->where('status','review')->count(),
            'done'        => $projects->where('status','done')->count(),
        ];
        
        // Hitung rata-rata dari overall_progress (accessor) bukan kolom progress manual
        $avgProgress = $total > 0 
            ? (int) round($projects->avg('overall_progress')) 
            : 0;

        // 5. Kalkulasi Chart Data (Monthly Series)
        // Logika sama persis: maks 12 bulan
        $firstStart = $projects->min('start_date');
        $lastDate   = $projects->max('end_date') ?? $projects->max('start_date');

        if (!$firstStart) {
            $from = ($start ?: now()->copy()->subMonths(5))->startOfMonth();
            $to   = ($end   ?: now())->endOfMonth();
        } else {
            $from = ($start ?: Carbon::parse($firstStart))->copy()->startOfMonth();
            $to   = ($end   ?: Carbon::parse($lastDate ?: $firstStart))->copy()->endOfMonth();
        }

        if ($from->diffInMonths($to) > 11) {
            $from = $to->copy()->subMonths(11)->startOfMonth();
        }

        $period = CarbonPeriod::create($from, '1 month', $to);
        
        $chartData = [];
        $monthlyLabels = [];
        $monthlyValues = [];

        foreach ($period as $m) {
            $key = $m->format('Y-m');
            $label = $m->translatedFormat('M Y');
            $count = $projects->filter(fn($p) => optional($p->start_date)?->format('Y-m') === $key)->count();

            // Format array terpisah (opsi 1)
            $monthlyLabels[] = $label;
            $monthlyValues[] = $count;

            // Format object (opsi 2 - lebih enak buat flutter map)
            $chartData[] = [
                'label' => $label,
                'count' => $count,
                'date_key' => $key // jaga2 butuh key asli
            ];
        }

        // 6. Return JSON Response
        return response()->json([
            'status' => true,
            'message' => 'Report data retrieved',
            'data' => [
                'filter' => [
                    'start' => $start?->toDateString(),
                    'end'   => $end?->toDateString(),
                ],
                'statistics' => [
                    'total_projects' => $total,
                    'average_progress' => $avgProgress,
                    'status_breakdown' => $statusCounts,
                ],
                'chart' => [
                    'labels' => $monthlyLabels, // Array string ["Jan 2025", "Feb 2025"]
                    'values' => $monthlyValues, // Array int [5, 2]
                    'details' => $chartData     // Array object lengkap
                ],
                'projects_list' => $projects->makeHidden(['sdlc_progress']) // Sembunyikan detail SDLC biar ringan (opsional)
            ]
        ], 200);
    }
}