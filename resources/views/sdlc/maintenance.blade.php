@php
  use Illuminate\Support\Str;
  $mains = $project->maintenances()->latest()->get();
@endphp

{{-- ================= HERO ================= --}}
<div class="testing-hero">
  <div class="testing-hero__title">
    Pantau Proses Maintenance Sistem
  </div>

  <button class="testing-hero__btn" id="openMaintenanceModal">
    Tambah Maintenance
  </button>
</div>

@if(session('ok'))
  <div class="flash-ok">{{ session('ok') }}</div>
@endif

{{-- ================= TABLE ================= --}}
<div class="card">
  <div class="card-title">Maintenance Log</div>

  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Judul</th>
        <th>Status</th>
        <th>Presentase</th>
        <th>PIC</th>
        <th>Mulai</th>
        <th>Selesai</th>
        <th>Catatan</th>
        <th>Aksi</th>
      </tr>
    </thead>

    <tbody>
    @forelse($mains as $i => $m)
      <tr>
        <td>{{ $i+1 }}</td>
        <td class="td-title">{{ $m->title }}</td>

        <td>
          <span class="status-badge status-{{ Str::slug($m->status) }}">
            {{ $m->status }}
          </span>
        </td>

        <td>
          <div class="progress-row">
            <div class="progress-wrap">
              <div class="progress-bar"
                   style="width:{{ $m->progress_percentage }}%"></div>
            </div>
            <span class="progress-value">
              {{ $m->progress_percentage }}%
            </span>
          </div>
        </td>

        <td>{{ $m->assignee ?? '—' }}</td>
        <td>{{ $m->opened_at?->format('d M Y H:i') ?? '—' }}</td>
        <td>{{ $m->closed_at?->format('d M Y H:i') ?? '—' }}</td>
        <td>{{ $m->notes ?? '—' }}</td>

        <td class="actions">
          <button class="btn-edit"
                  onclick='openEdit(@json($m))'>
            Edit
          </button>

          <form method="POST"
                action="{{ route('maintenances.destroy', $m->id) }}"
                onsubmit="return confirm('Hapus maintenance ini?')">
            @csrf @method('DELETE')
            <button class="btn-delete">Hapus</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="9">Belum ada maintenance.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

{{-- ================= MODAL ================= --}}
<div class="tc-modal" id="maintenanceModal">
  <div class="tc-modal__backdrop"></div>

  <div class="tc-modal__panel">
    <div class="tc-modal__head">
      <div class="tc-modal__title" id="modalTitle">
        Tambah Maintenance
      </div>
      <button class="tc-modal__close" id="closeMaintenance">
        ✕
      </button>
    </div>

    <form id="maintenanceForm"
          method="POST"
          action="{{ route('projects.maintenances.store', $project->id) }}"
          class="tc-form">
      @csrf
      <input type="hidden" name="_method" id="method" value="POST">

      <input name="title"
             class="tc-input"
             placeholder="Judul maintenance"
             required>

      <select name="status" class="tc-input" required>
        <option>Planned</option>
        <option>In Progress</option>
        <option>Resolved</option>
        <option>Closed</option>
      </select>

      <input name="assignee"
             class="tc-input"
             placeholder="PIC / Penanggung jawab">

      <div class="tc-row">
        <input type="datetime-local"
               name="opened_at"
               class="tc-input">

        <input type="datetime-local"
               name="closed_at"
               class="tc-input">
      </div>

      <textarea name="notes"
                class="tc-input tc-textarea"
                placeholder="Catatan maintenance"></textarea>

      <button class="tc-submit">
        Simpan Maintenance
      </button>
    </form>
  </div>
</div>

{{-- ================= STYLE ================= --}}
<style>
:root{
  --brand:#F69220;
  --r:16px;
  --shadow:0 10px 30px rgba(15,23,42,.12);
}

/* Hero */
.testing-hero{
  background:var(--brand);
  color:#fff;
  padding:16px;
  border-radius:var(--r);
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.testing-hero__title{font-weight:800}
.testing-hero__btn{
  background:#fff;
  color:var(--brand);
  padding:10px 16px;
  border-radius:999px;
  font-weight:800;
  cursor:pointer;
}

/* Card */
.card{
  margin-top:16px;
  background:#fff;
  border-radius:var(--r);
  padding:16px;
  box-shadow:var(--shadow);
}
.card-title{font-weight:800;margin-bottom:8px}

/* Table */
.table{width:100%;border-collapse:collapse}
.table th,.table td{
  padding:12px 10px;
  border-bottom:1px solid #eee;
}
.td-title{font-weight:600}

/* Status */
.status-badge{
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
}
.status-planned{background:#f1f5f9}
.status-in-progress{background:#ffedd5}
.status-resolved{background:#dcfce7}
.status-closed{background:#e5e7eb}

/* Progress */
.progress-row{
  display:flex;
  align-items:center;
  gap:8px;
}
.progress-wrap{
  width:90px;
  height:6px;
  background:#e5e7eb;
  border-radius:999px;
}
.progress-bar{
  height:100%;
  background:var(--brand);
}
.progress-value{
  font-size:12px;
  font-weight:700;
}

/* Actions */
.actions{display:flex;gap:6px}
.btn-edit{
  background:#e0f2fe;
  border:1px solid #bae6fd;
  padding:6px 10px;
  border-radius:8px;
}
.btn-delete{
  background:#fee2e2;
  border:1px solid #fecaca;
  padding:6px 10px;
  border-radius:8px;
}

/* Modal */
.tc-modal{position:fixed;inset:0;display:none;z-index:9999}
.tc-modal.is-open{display:block}
.tc-modal__backdrop{
  position:absolute;inset:0;
  background:rgba(0,0,0,.45);
}
.tc-modal__panel{
  background:#fff;
  position:absolute;
  top:50%;left:50%;
  transform:translate(-50%,-50%);
  padding:18px;
  border-radius:var(--r);
  width:500px;
  box-shadow:var(--shadow);
}
.tc-modal__head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:12px;
}
.tc-modal__close{
  background:none;border:none;
  font-size:20px;cursor:pointer;
}

/* Form */
.tc-form{display:flex;flex-direction:column;gap:12px}
.tc-row{display:flex;gap:8px}
.tc-input{
  padding:12px;
  border-radius:12px;
  border:1px solid #ddd;
  width:100%;
}
.tc-textarea{min-height:90px}
.tc-submit{
  background:var(--brand);
  color:#fff;
  padding:14px;
  border-radius:12px;
  font-weight:900;
  cursor:pointer;
}

/* Flash */
.flash-ok{
  margin-top:12px;
  background:#ecfdf5;
  padding:12px;
  border-radius:12px;
  color:#065f46;
}
</style>

{{-- ================= SCRIPT ================= --}}
<script>
const modal = document.getElementById('maintenanceModal');
const form  = document.getElementById('maintenanceForm');

openMaintenanceModal.onclick = () => {
  form.reset();
  form.action = "{{ route('projects.maintenances.store', $project->id) }}";
  method.value = 'POST';
  modalTitle.innerText = 'Tambah Maintenance';
  modal.classList.add('is-open');
};

closeMaintenance.onclick = () =>
  modal.classList.remove('is-open');

modal.querySelector('.tc-modal__backdrop').onclick =
  () => modal.classList.remove('is-open');

function openEdit(m){
  modal.classList.add('is-open');
  form.action = `/maintenances/${m.id}`;
  method.value = 'PUT';
  modalTitle.innerText = 'Edit Maintenance';

  form.title.value = m.title;
  form.status.value = m.status;
  form.assignee.value = m.assignee ?? '';
  form.opened_at.value = m.opened_at?.replace(' ', 'T') ?? '';
  form.closed_at.value = m.closed_at?.replace(' ', 'T') ?? '';
  form.notes.value = m.notes ?? '';
}
</script>
