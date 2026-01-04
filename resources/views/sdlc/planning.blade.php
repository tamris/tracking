@php
  use Illuminate\Support\Carbon;

  $start = $project->start_date
      ? Carbon::parse($project->start_date)
      : now();

  $end = $project->end_date
      ? Carbon::parse($project->end_date)
      : (clone $start)->addMonths(6);

  $months = [];
  $cursor = $start->copy()->startOfMonth();
  $endM   = $end->copy()->startOfMonth();

  while ($cursor <= $endM) {
    $months[] = $cursor->copy();
    $cursor->addMonth();
  }
@endphp

<style>
:root{
  --brand:#F69220;
  --ink:#111827;
  --muted:#6b7280;
  --card:#ffffff;
  --shadow:0 10px 24px rgba(15,23,42,.06);
}
.plan-wrap{display:flex;flex-direction:column;gap:14px}
.plan-grid-top{display:grid;grid-template-columns:1.2fr .8fr;gap:14px}
.p-card{background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:14px;box-shadow:var(--shadow);padding:14px}
.p-title{font-weight:800;margin-bottom:10px}
.p-table{width:100%;border-collapse:collapse;font-size:14px}
.p-table td{padding:7px 6px;border-bottom:1px solid rgba(15,23,42,.06)}
.p-table td:first-child{color:var(--muted);width:140px}

.tl{position:relative;padding-left:20px}
.tl:before{content:"";position:absolute;left:8px;top:6px;bottom:6px;width:3px;background:rgba(246,146,32,.35)}
.tl-item{position:relative;padding:6px 0 10px}
.tl-dot{position:absolute;left:-3px;top:10px;width:14px;height:14px;border-radius:50%;background:var(--brand)}

.months{display:flex;gap:10px;flex-wrap:wrap}
.m-box{width:92px;border:1px solid rgba(15,23,42,.08);border-radius:10px;padding:8px}
.m-name{font-weight:800;font-size:12px}

.upd{display:grid;gap:10px;margin-top:10px}
.inp{border-radius:12px;border:1px solid rgba(15,23,42,.1);padding:12px}
.btn-brand2{background:var(--brand);color:#fff;border:none;border-radius:12px;padding:12px;font-weight:800}

.file-list{display:flex;flex-wrap:wrap;gap:8px}
.file-chip{display:flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:999px;padding:6px 10px;font-size:13px}

@media(max-width:900px){
  .plan-grid-top{grid-template-columns:1fr}
}
</style>

<div class="plan-wrap">

  {{-- FLASH --}}
  @if(session('ok'))
    <div class="p-card" style="background:#ecfdf5;border-color:#a7f3d0;color:#065f46">
      {{ session('ok') }}
    </div>
  @endif

  {{-- ================= RINGKASAN + AKTIVITAS ================= --}}
  <div class="plan-grid-top">

    {{-- RINGKASAN --}}
    <div class="p-card">
      <div class="p-title">Ringkasan Project</div>
      <table class="p-table">
        <tr><td>Nama</td><td>{{ $project->title }}</td></tr>
        <tr><td>PIC</td><td>{{ $project->pic ?? '—' }}</td></tr>
        <tr><td>Status</td><td>{{ $project->status ?? '—' }}</td></tr>
        <tr><td>Mulai</td><td>{{ $project->start_date ? Carbon::parse($project->start_date)->format('d M Y') : '—' }}</td></tr>
        <tr><td>Selesai</td><td>{{ $project->end_date ? Carbon::parse($project->end_date)->format('d M Y') : '—' }}</td></tr>
        <tr>
          <td>Kontrak</td>
          <td>{{ $project->contract_file_name ?? '—' }}</td>
        </tr>
        <tr><td>Kegiatan</td><td>{{ $project->activity ?? '—' }}</td></tr>
      </table>

      {{-- UPDATE PLANNING --}}
      <form class="upd"
            method="POST"
            action="{{ route('projects.planning.update', $project->id) }}"
            enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <input class="inp"
               name="planning_activity"
               placeholder="Update kegiatan planning"
               value="{{ old('planning_activity', $project->activity) }}">

        <textarea class="inp"
                  name="planning_note"
                  rows="3"
                  placeholder="Catatan planning">{{ old('planning_note', $project->planning_note) }}</textarea>

        <input class="inp" type="file" name="files[]" multiple>

        <button class="btn-brand2">Update Planning</button>
      </form>
    </div>

    {{-- TIMELINE AKTIVITAS --}}
    <div class="p-card">
      <div class="p-title">Timeline Aktivitas</div>

      @if(($activities ?? collect())->isEmpty())
        <div style="color:#6b7280">Belum ada aktivitas.</div>
      @else
        <div class="tl">
          @foreach($activities as $a)
            <div class="tl-item">
              <div class="tl-dot"></div>
              <div style="font-size:12px;color:#6b7280">
                {{ $a->occurred_at?->format('d M Y') }}
              </div>
              <div style="font-weight:700">{{ $a->title }}</div>
              @if($a->description)
                <div style="font-size:12px;color:#6b7280">{{ $a->description }}</div>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  {{-- ================= TIMELINE PROJECT ================= --}}
  <div class="p-card">
    <div class="p-title">Timeline Project</div>
    <div class="months">
      @foreach($months as $m)
        <div class="m-box">
          <div class="m-name">{{ $m->format('M') }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- ================= DOKUMEN ================= --}}
  <div class="p-card">
    <div class="p-title">Dokumen / Catatan</div>

    <div class="doc-box">{{ $project->planning_note ?? '—' }}</div>

    @if(($planningFiles ?? collect())->count())
      <div class="file-list">
        @foreach($planningFiles as $f)
          <div class="file-chip">
            📄 {{ $f->original_name }}
            <a href="{{ asset('storage/'.$f->path) }}" target="_blank">Lihat</a>
            <form method="POST"
                  action="{{ route('project-files.destroy', $f->id) }}"
                  onsubmit="return confirm('Hapus file ini?')">
              @csrf @method('DELETE')
              <button style="background:#fee2e2;border:none;border-radius:6px">Hapus</button>
            </form>
          </div>
        @endforeach
      </div>
    @else
      <div style="color:#6b7280">Belum ada dokumen.</div>
    @endif
  </div>

</div>
