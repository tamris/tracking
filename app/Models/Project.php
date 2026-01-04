<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use HasFactory;

    /* =====================================================
     | FILLABLE & CAST
     ===================================================== */
    protected $fillable = [
        'user_id',
        'title',
        'pic',
        'status',
        'start_date',
        'end_date',
        'progress',
        'activity',
        'planning_note',
        'contract_file',
        'contract_file_name',
        'cover_image',
        'cover_image_name',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'progress'   => 'integer',
    ];

    /* =====================================================
     | SCOPE
     ===================================================== */
    public function scopeMine($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /* =====================================================
     | SDLC RELATIONS
     ===================================================== */
    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class)->latest();
    }

    public function designSpecs(): HasMany
    {
        return $this->hasMany(DesignSpec::class)->latest();
    }

    public function developments(): HasMany
    {
        return $this->hasMany(Development::class)->latest();
    }

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class)->latest();
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class)->latest();
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class)->latest();
    }

    /* =====================================================
     | PLANNING / GENERAL
     ===================================================== */
    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function planningActivities(): HasMany
    {
        return $this->activities()
            ->where('phase', 'planning')
            ->latest('occurred_at');
    }

    public function planningFiles(): HasMany
    {
        return $this->files()
            ->where('phase', 'planning')
            ->latest();
    }

    /* =====================================================
     | FILE ACCESSOR
     ===================================================== */
    public function getContractFileUrlAttribute(): ?string
    {
        return $this->contract_file
            ? Storage::url($this->contract_file)
            : null;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image
            ? Storage::url($this->cover_image)
            : null;
    }

    /* =====================================================
     | SDLC PROGRESS (TANPA PLANNING)
     ===================================================== */
    public function getSdlcProgressAttribute(): array
    {
        return [
            'requirement' => $this->requirementProgress(),
            'design'      => $this->designProgress(),
            'development' => $this->developmentProgress(),
            'testing'     => $this->testingProgress(),
            'deployment'  => $this->deploymentProgress(),
            'maintenance' => $this->maintenanceProgress(),
        ];
    }

    /* ================= DETAIL CALCULATION ================= */

    protected function requirementProgress(): int
    {
        $total = $this->requirements()->count();
        if ($total === 0) return 0;

        $done       = $this->requirements()->where('status', 'Done')->count();
        $inProgress = $this->requirements()->where('status', 'In Progress')->count();

        $score = ($done * 100) + ($inProgress * 50);

        return (int) round($score / $total);
    }

    protected function designProgress(): int
    {
        $total = $this->designSpecs()->count();
        if ($total === 0) return 0;

        $approved = $this->designSpecs()
            ->where('status', 'Approved')
            ->count();

        return (int) round(($approved / $total) * 100);
    }

    protected function developmentProgress(): int
    {
        $totalDesign = $this->designSpecs()->count();
        if ($totalDesign === 0) return 0;

        $done = $this->developments()
            ->where('status', 'Done')
            ->distinct('design_spec_id')
            ->count('design_spec_id');

        return (int) round(($done / $totalDesign) * 100);
    }

    protected function testingProgress(): int
    {
        $total = $this->testCases()->count();
        if ($total === 0) return 0;

        $passed = $this->testCases()
            ->where('status', 'Passed')
            ->count();

        return (int) round(($passed / $total) * 100);
    }

    protected function deploymentProgress(): int
    {
        // mengikuti testing (logika kamu sudah benar)
        return $this->testingProgress();
    }

    protected function maintenanceProgress(): int
    {
        return $this->status === 'done'
            ? 100
            : $this->deploymentProgress();
    }

    /* =====================================================
     | OVERALL PROGRESS (TANPA PLANNING)
     ===================================================== */
    public function getOverallProgressAttribute(): int
    {
        $progress = $this->sdlc_progress;

        if (empty($progress)) {
            return 0;
        }

        return (int) round(
            array_sum($progress) / count($progress)
        );
    }
}
