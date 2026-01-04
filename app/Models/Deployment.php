<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'environment',
        'version',
        'status',
        'pic',
        'start_at',
        'end_at',
        'url',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /* =========================
       ACCESSOR PROGRESS (%)
    ========================= */
    public function getProgressPercentageAttribute(): int
    {
        return match ($this->status) {
            'Planned'     => 0,
            'In Progress' => 50,
            'Success'     => 100,
            'Failed'      => 100,
            default       => 0,
        };
    }
}
