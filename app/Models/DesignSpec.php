<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignSpec extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'requirement_id',
        'artifact_type',
        'artifact_name',
        'reference_url',
        'rationale',
        'status',
        'pic',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // ❌ JANGAN gunakan $with di sini (hindari timeout / query berat)

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
