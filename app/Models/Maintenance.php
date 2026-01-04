<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'status',
        'assignee',
        'progress_percentage',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /* =========================
       LOGIKA PROGRESS OTOMATIS
    ========================= */
    public static function progressFromStatus(string $status): int
    {
        return match ($status) {
            'Planned'     => 0,
            'In Progress' => 50,
            'Resolved',
            'Closed'      => 100,
            default       => 0,
        };
    }
}
