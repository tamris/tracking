@extends('layouts.app')
@section('title','Detail Project')

@push('styles')
  @vite('resources/css/project-detail.css')
@endpush

@section('content')
@php
  /* ================= SDLC MAP ================= */
  $phaseMap = [
    'planning'    => 'Planning',
    'requirement' => 'Requirement',
    'design'      => 'Design',
    'development' => 'Development',
    'testing'     => 'Testing',
    'deployment'  => 'Deployment',
    'maintenance' => 'Maintenance',
  ];

  /* ================= ACTIVE PHASE ================= */
  $phaseKey = strtolower($phase ?? 'planning');
  if (!array_key_exists($phaseKey, $phaseMap)) {
    $phaseKey = 'planning';
  }

  /* ================= SDLC PROGRESS ================= */
  $raw = $project->sdlc_progress ?? [];

  $sdlc = [
    'planning'    => null,
    'requirement' => $raw['requirement'] ?? 0,
    'design'      => $raw['design'] ?? 0,
    'development' => $raw['development'] ?? 0,
    'testing'     => null,
    'deployment'  => null,
    'maintenance' => null,
  ];

  /* ================= OVERALL ================= */
  $overall = $project->overall_progress;

  /* ================= STATUS ================= */
  $statusMap = [
    'todo'        => ['Belum Mulai', 'pill todo'],
    'in_progress' => ['In Progress', 'pill in-progress'],
    'review'      => ['Review',      'pill review'],
    'done'        => ['Selesai',     'pill selesai'],
  ];
  [$statusLabel, $statusClass] = $statusMap[$project->status] ?? ['-', 'pill'];

  /* ================= FILE ================= */
  $contractUrl = $project->contract_file_url;

  /* phase tanpa progress */
  $noProgressPhases = ['planning','testing','deployment','maintenance'];
@endphp

<section class="project-detail">

  {{-- HEADER --}}
  <header class="detail-hero card">
    <div class="left">
      <h1 class="title">{{ $project->title }}</h1>
      <p class="subtitle">{{ $project->activity ?: '-' }}</p>
      <div class="meta">
        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
        <span>• PIC: <strong>{{ $project->pic ?: '-' }}</strong></span>
      </div>
    </div>
  </header>

  {{-- KPI --}}
  <div class="cards-3">
    <div class="card kpi">
      <div class="kpi-label">Progress</div>
      <div class="kpi-value">{{ $overall }}%</div>
      <div class="progress">
        <div class="bar" style="width: {{ $overall }}%"></div>
      </div>
    </div>

    <div class="card kpi">
      <div class="kpi-label">Dokumen Kontrak</div>
      <div class="kpi-value">{{ $contractUrl ? 1 : 0 }}</div>
      <div class="muted">{{ $contractUrl ? 'File terunggah' : 'Belum ada' }}</div>
    </div>

    <div class="card kpi">
      <div class="kpi-label">Status</div>
      <div class="kpi-value">{{ $statusLabel }}</div>
    </div>
  </div>

  {{-- ================= SDLC STEPPER ================= --}}
  <div class="card stepper">
    <ul class="steps">
      @foreach ($phaseMap as $k => $label)
        @php $p = $sdlc[$k]; @endphp

        <li class="step
          {{ $phaseKey === $k ? 'active' : '' }}
          {{ in_array($k, $noProgressPhases) ? 'no-progress' : '' }}
        ">
          <a href="{{ route('projects.sdlc', [$project->id, $k]) }}">

            <div class="step-label-row">
              <span>{{ $label }}</span>
              @if ($p !== null)
                <span class="step-percent">{{ $p }}%</span>
              @endif
            </div>

            @if ($p !== null)
              <div class="step-bar">
                <span class="fill" style="width: {{ $p }}%"></span>
              </div>
            @endif

          </a>
        </li>
      @endforeach
    </ul>
  </div>

  {{-- CONTENT --}}
  @includeIf('sdlc.' . $phaseKey)

</section>
@endsection
