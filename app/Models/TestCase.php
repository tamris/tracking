<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'requirement_id',
        'design_spec_id',
        'title',
        'scenario',
        'expected_result',
        'status',
        'tester',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function designSpec(): BelongsTo
    {
        return $this->belongsTo(DesignSpec::class);
    }

    /* =========================
       ACCESSOR PROGRESS (%)
    ========================= */
    public function getProgressPercentageAttribute(): int
    {
        return match ($this->status) {
            'Planned'     => 0,
            'In Progress' => 50,
            'Passed'      => 100,
            'Failed'      => 0,
            default       => 0,
        };
    }
}
