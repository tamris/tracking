@php
  use Illuminate\Support\Str;
  use Carbon\Carbon;

  $deps = $project->deployments()->latest()->get();
@endphp

{{-- ================= HERO ================= --}}
<div class="testing-hero">
  <div class="testing-hero__title">Pantau Proses Deployment Sistem</div>
  <button class="testing-hero__btn" id="openDeploymentModal">
    Tambah Deployment
  </button>
</div>

@if(session('ok'))
  <div class="flash-ok">{{ session('ok') }}</div>
@endif

{{-- ================= TABLE ================= --}}
<div class="card">
  <div class="card-title">Deployment Log</div>

  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Environment</th>
        <th>Versi</th>
        <th>Status</th>
        <th>Presentase</th>
        <th>PIC</th>
        <th>Mulai</th>
        <th>Selesai</th>
        <th style="width:150px">Aksi</th>
      </tr>
    </thead>

    <tbody>
    @forelse($deps as $i => $d)
      <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $d->environment }}</td>
        <td>{{ $d->version ?? '—' }}</td>

        <td>
          <span class="status status-{{ Str::slug($d->status) }}">
            {{ $d->status }}
          </span>
        </td>

        <td>
          <div class="progress-row">
            <div class="progress-wrap">
              <div class="progress-bar"
                   style="width:{{ $d->progress_percentage }}%"></div>
            </div>
            <small>{{ $d->progress_percentage }}%</small>
          </div>
        </td>

        <td>{{ $d->pic ?? '—' }}</td>
        <td>{{ $d->start_at?->format('d M Y H:i') ?? '—' }}</td>
        <td>{{ $d->end_at?->format('d M Y H:i') ?? '—' }}</td>

        <td class="actions">
          <button class="btn-edit"
            onclick='openEditDeployment(@json($d))'>
            Edit
          </button>

          <form method="POST"
            action="{{ route('deployments.destroy',$d->id) }}"
            onsubmit="return confirm('Hapus deployment ini?')">
            @csrf @method('DELETE')
            <button class="btn-delete">Hapus</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="9">Belum ada deployment.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

{{-- ================= MODAL ================= --}}
<div class="tc-modal" id="deploymentModal">
  <div class="tc-modal__backdrop"></div>

  <div class="tc-modal__panel">
    <div class="tc-modal__head">
      <strong id="modalTitle">Tambah Deployment</strong>
      <button id="closeDeployment">✕</button>
    </div>

    <form method="POST"
          id="deploymentForm"
          action="{{ route('projects.deployments.store',$project->id) }}"
          class="tc-form">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">

      <label>Environment</label>
      <select name="environment" id="dp_environment" required>
        <option>Dev</option>
        <option>Staging</option>
        <option>Production</option>
      </select>

      <label>Versi / Aktivitas</label>
      <input name="version" id="dp_version" placeholder="v1.2.0">

      <label>Status</label>
      <select name="status" id="dp_status">
        <option>Planned</option>
        <option>In Progress</option>
        <option>Success</option>
        <option>Failed</option>
      </select>

      <label>Presentase (%)</label>
      <input type="number"
             name="progress_percentage"
             id="dp_progress"
             min="0" max="100"
             placeholder="0 - 100">

      <label>PIC</label>
      <input name="pic" id="dp_pic" placeholder="Nama penanggung jawab">

      <div class="grid-2">
        <div>
          <label>Waktu Mulai</label>
          <input type="datetime-local" name="start_at" id="dp_start_at">
        </div>
        <div>
          <label>Waktu Selesai</label>
          <input type="datetime-local" name="end_at" id="dp_end_at">
        </div>
      </div>

      <label>URL Aplikasi</label>
      <input type="url" name="url" id="dp_url" placeholder="https://example.com">

      <label>Catatan</label>
      <textarea name="notes" id="dp_notes"></textarea>

      <button class="btn-primary">Simpan Deployment</button>
    </form>
  </div>
</div>

{{-- ================= STYLE ================= --}}
<style>
:root{--brand:#F69220}
body.modal-open{overflow:hidden}

.testing-hero{
  background:var(--brand);
  color:#fff;
  padding:16px;
  border-radius:16px;
  margin-top:16px;
  display:flex;
  justify-content:space-between;
}
.testing-hero__btn{
  background:#fff;
  color:var(--brand);
  padding:10px 16px;
  border-radius:999px;
  font-weight:800;
}

.card{
  background:#fff;
  margin-top:16px;
  padding:16px;
  border-radius:16px;
}
.card-title{font-weight:800;margin-bottom:12px}

.table{width:100%;border-collapse:collapse}
.table th,.table td{
  padding:10px;border-bottom:1px solid #eee
}

.status{
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
}
.status-in-progress{background:#ffedd5}
.status-success{background:#dcfce7}

.progress-row{display:flex;gap:6px;align-items:center}
.progress-wrap{
  width:90px;height:6px;
  background:#e5e7eb;border-radius:999px
}
.progress-bar{height:100%;background:var(--brand)}

.actions{display:flex;gap:6px}
.btn-edit{
  background:#e0f2fe;border:1px solid #bae6fd;
  padding:6px 10px;border-radius:8px
}
.btn-delete{
  background:#fee2e2;border:1px solid #fecaca;
  padding:6px 10px;border-radius:8px
}

.tc-modal{
  position:fixed;inset:0;display:none;z-index:99999;
}
.tc-modal.is-open{display:block}
.tc-modal__backdrop{
  position:absolute;inset:0;
  background:rgba(15,23,42,.55);
}
.tc-modal__panel{
  position:absolute;
  top:50%;left:50%;
  transform:translate(-50%,-50%);
  background:#fff;
  width:520px;max-width:95vw;
  padding:20px;border-radius:18px;
}
.tc-modal__head{
  display:flex;justify-content:space-between;margin-bottom:12px;
}

.tc-form{
  display:flex;flex-direction:column;gap:10px;
}
.tc-form input,
.tc-form select,
.tc-form textarea{
  padding:12px;border-radius:12px;border:1px solid #ddd;
}

.grid-2{
  display:grid;grid-template-columns:1fr 1fr;gap:10px;
}

.btn-primary{
  background:var(--brand);
  color:#fff;
  padding:14px;
  border-radius:12px;
  font-weight:800;
}

.flash-ok{
  background:#ecfdf5;
  padding:12px;border-radius:12px;margin-top:12px;
}
</style>

{{-- ================= SCRIPT ================= --}}
<script>
const modal = document.getElementById('deploymentModal');
const body  = document.body;

openDeploymentModal.onclick = () => {
  deploymentForm.reset();
  deploymentForm.action =
    "{{ route('projects.deployments.store',$project->id) }}";
  formMethod.value = 'POST';
  modalTitle.innerText = 'Tambah Deployment';
  modal.classList.add('is-open');
  body.classList.add('modal-open');
};

closeDeployment.onclick =
modal.querySelector('.tc-modal__backdrop').onclick = closeModal;

function closeModal(){
  modal.classList.remove('is-open');
  body.classList.remove('modal-open');
}

window.openEditDeployment = d => {
  deploymentForm.action = `/deployments/${d.id}`;
  formMethod.value = 'PUT';
  modalTitle.innerText = 'Edit Deployment';

  dp_environment.value = d.environment;
  dp_version.value = d.version ?? '';
  dp_status.value = d.status;
  dp_progress.value = d.progress_percentage ?? 0;
  dp_pic.value = d.pic ?? '';
  dp_start_at.value = d.start_at ? d.start_at.replace(' ','T') : '';
  dp_end_at.value = d.end_at ? d.end_at.replace(' ','T') : '';
  dp_url.value = d.url ?? '';
  dp_notes.value = d.notes ?? '';

  modal.classList.add('is-open');
  body.classList.add('modal-open');
};
</script>
