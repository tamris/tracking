@php use Illuminate\Support\Str; @endphp

{{-- ================= HEADER ================= --}}
<div class="progress-banner">
  <span>Pantau Progres Pekerjaanmu:</span>
  <button class="btn-add-white" onclick="openAddModal()">
    Tambah Requirement
  </button>
</div>

{{-- ================= ALERT ================= --}}
@if(session('ok'))
  <div class="alert success">{{ session('ok') }}</div>
@endif

{{-- ================= TABLE ================= --}}
<div class="card req">
  <div class="table-wrap req-table">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Judul</th>
          <th>PIC</th>
          <th>Status</th>
          <th>Mulai</th>
          <th>Selesai</th>
          <th style="width:160px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($project->requirements as $i => $r)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $r->title }}</td>
          <td>{{ $r->pic ?? '-' }}</td>
          <td>{{ $r->status }}</td>
          <td>{{ $r->start_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $r->end_date?->format('d M Y') ?? '-' }}</td>
          <td class="aksi">

            {{-- EDIT --}}
            <button class="btn-edit"
              onclick='openEditModal(
                {{ $r->id }},
                @json($r->title),
                "{{ $r->type }}",
                "{{ $r->priority }}",
                "{{ $r->status }}",
                @json($r->pic),
                "{{ optional($r->start_date)->format('Y-m-d') }}",
                "{{ optional($r->end_date)->format('Y-m-d') }}",
                @json($r->acceptance_criteria)
              )'>
              Edit
            </button>

            {{-- DELETE --}}
            <form method="POST"
              action="{{ route('projects.requirements.destroy', [$project->id, $r->id]) }}"
              onsubmit="return confirm('Hapus requirement ini?')">
              @csrf
              @method('DELETE')
              <button class="btn-delete">Hapus</button>
            </form>

          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="muted">Belum ada requirement</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ================= MODAL TAMBAH ================= --}}
<div class="modal" id="addModal">
  <div class="modal-box modal-lg">

    <div class="modal-header">
      <h3>Tambah Requirement</h3>
      <button onclick="closeAddModal()">✕</button>
    </div>

    <form method="POST"
          action="{{ route('projects.requirements.store', $project->id) }}"
          class="stack modal-form">
      @csrf

      {{-- INFORMASI UTAMA --}}
      <div class="form-section">
        <h4>Informasi Utama</h4>

        <input class="i" name="title"
          placeholder="Judul requirement" required>

        <div class="row-3">
          <select class="i" name="type">
            <option value="FR">FR</option>
            <option value="NFR">NFR</option>
          </select>

          <select class="i" name="priority">
            <option>Low</option>
            <option selected>Medium</option>
            <option>High</option>
          </select>

          <select class="i" name="status">
            <option selected>Planned</option>
            <option>In Progress</option>
            <option>Done</option>
          </select>
        </div>
      </div>

      {{-- PIC --}}
      <div class="form-section">
        <h4>Penanggung Jawab</h4>
        <input class="i" name="pic"
          placeholder="PIC / Penanggung jawab">
      </div>

      {{-- WAKTU --}}
      <div class="form-section">
        <h4>Timeline</h4>
        <div class="row-2">
          <div>
            <label class="label">Tanggal Mulai</label>
            <input class="i" type="date" name="start_date">
          </div>
          <div>
            <label class="label">Tanggal Selesai</label>
            <input class="i" type="date" name="end_date">
          </div>
        </div>
      </div>

      {{-- ACCEPTANCE --}}
      <div class="form-section">
        <h4>Acceptance Criteria</h4>
        <textarea class="i" name="acceptance_criteria"
          rows="4" placeholder="Acceptance criteria (opsional)"></textarea>
      </div>

      <button class="btn btn-brand btn-full">
        Simpan Requirement
      </button>
    </form>
  </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal" id="editModal">
  <div class="modal-box modal-lg">

    <div class="modal-header">
      <h3>Edit Requirement</h3>
      <button onclick="closeEditModal()">✕</button>
    </div>

    <form method="POST" id="editForm"
          class="stack modal-form">
      @csrf
      @method('PUT')

      <div class="form-section">
        <h4>Informasi Utama</h4>

        <input class="i" name="title" id="e_title" required>

        <div class="row-3">
          <select class="i" name="type" id="e_type">
            <option value="FR">FR</option>
            <option value="NFR">NFR</option>
          </select>

          <select class="i" name="priority" id="e_priority">
            <option>Low</option>
            <option>Medium</option>
            <option>High</option>
          </select>

          <select class="i" name="status" id="e_status">
            <option>Planned</option>
            <option>In Progress</option>
            <option>Done</option>
          </select>
        </div>
      </div>

      <div class="form-section">
        <h4>Penanggung Jawab</h4>
        <input class="i" name="pic" id="e_pic">
      </div>

      <div class="form-section">
        <h4>Timeline</h4>
        <div class="row-2">
          <input class="i" type="date" name="start_date" id="e_start">
          <input class="i" type="date" name="end_date" id="e_end">
        </div>
      </div>

      <div class="form-section">
        <h4>Acceptance Criteria</h4>
        <textarea class="i" name="acceptance_criteria"
          id="e_acceptance" rows="4"></textarea>
      </div>

      <button class="btn btn-brand btn-full">
        Simpan Perubahan
      </button>
    </form>
  </div>
</div>

{{-- ================= CSS ================= --}}
<style>
.progress-banner {
  background: linear-gradient(90deg,#ff9800,#ffa726);
  color:#fff;
  padding:18px 24px;
  border-radius:18px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  font-weight:600;
  margin-bottom:20px;
}

.btn-add-white {
  background:#fff;
  color:#ff9800;
  border:none;
  padding:10px 22px;
  border-radius:999px;
  font-weight:600;
  cursor:pointer;
}

.aksi { display:flex; gap:8px; }

.btn-edit {
  background:#e8f3ff;
  color:#1976d2;
  border:1px solid #b6dcff;
  padding:6px 14px;
  border-radius:8px;
}

.btn-delete {
  background:#ffeaea;
  color:#d32f2f;
  border:1px solid #ffbcbc;
  padding:6px 14px;
  border-radius:8px;
}

.modal {
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.45);
  z-index:50;
  justify-content:center;
  align-items:center;
}

.modal-box {
  background:#fff;
  padding:20px;
  border-radius:18px;
}

.modal-lg { width:620px; max-width:95%; }

.modal-header {
  display:flex;
  justify-content:space-between;
  margin-bottom:10px;
}

.stack { display:flex; flex-direction:column; gap:12px; }

.form-section {
  background:#f9fafb;
  padding:14px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  gap:10px;
}

.row-2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }

.label { font-size:12px; color:#6b7280; }

.btn-full {
  width:100%;
  padding:12px;
  border-radius:999px;
}
</style>

{{-- ================= JS ================= --}}
<script>
function openAddModal(){ addModal.style.display='flex'; }
function closeAddModal(){ addModal.style.display='none'; }

function openEditModal(id,t,type,p,s,pic,sd,ed,acc){
  editForm.action = `/projects/{{ $project->id }}/requirements/${id}`;
  e_title.value=t; e_type.value=type;
  e_priority.value=p; e_status.value=s;
  e_pic.value=pic||''; e_start.value=sd||'';
  e_end.value=ed||''; e_acceptance.value=acc||'';
  editModal.style.display='flex';
}
function closeEditModal(){ editModal.style.display='none'; }
</script>
