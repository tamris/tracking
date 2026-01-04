@php
  use Illuminate\Support\Str;
  use Carbon\Carbon;

  $tests = $project->testCases()->latest()->get();
@endphp

{{-- ================= HERO ================= --}}
<div class="testing-hero">
  <div class="testing-hero__title">Pantau Progres Pekerjaanmu</div>
  <button class="testing-hero__btn" id="openTestModal">
    Tambah Testing
  </button>
</div>

@if(session('ok'))
  <div class="flash-success">{{ session('ok') }}</div>
@endif

{{-- ================= TABLE ================= --}}
<div class="card">
  <div class="card-title">Test Cases</div>

  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Judul</th>
        <th>Status</th>
        <th>Progress</th>
        <th>Mulai</th>
        <th>Selesai</th>
        <th>Tester</th>
        <th style="width:140px">Aksi</th>
      </tr>
    </thead>
    <tbody>
    @forelse($tests as $i => $t)
      <tr>
        <td>{{ $i+1 }}</td>
        <td class="td-title">{{ $t->title }}</td>

        <td>
          <span class="status status-{{ Str::slug($t->status) }}">
            {{ $t->status }}
          </span>
        </td>

        <td>
          <div class="progress-row">
            <div class="progress-bar">
              <span style="width:{{ $t->progress_percentage }}%"></span>
            </div>
            <small>{{ $t->progress_percentage }}%</small>
          </div>
        </td>

        <td>{{ $t->start_at ? Carbon::parse($t->start_at)->format('d M Y H:i') : '—' }}</td>
        <td>{{ $t->end_at ? Carbon::parse($t->end_at)->format('d M Y H:i') : '—' }}</td>
        <td>{{ $t->tester ?: '—' }}</td>

        <td class="actions">
          <button class="btn-edit"
            onclick='openEditTestCase(@json($t))'>Edit</button>

          <form method="POST"
            action="{{ route('test-cases.destroy', $t->id) }}"
            onsubmit="return confirm('Hapus test case ini?')">
            @csrf @method('DELETE')
            <button class="btn-delete">Hapus</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="8">Belum ada test case.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

{{-- ================= MODAL ================= --}}
<div class="modal" id="testCaseModal">
  <div class="modal-backdrop"></div>

  <div class="modal-panel">
    <div class="modal-head">
      <h3 id="modalTitle">Tambah Test Case</h3>
      <button id="closeTestModal">✕</button>
    </div>

    <form id="testCaseForm"
      method="POST"
      action="{{ route('projects.test-cases.store', $project->id) }}">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">

      {{-- Judul --}}
      <div class="form-group">
        <label>Judul Test Case</label>
        <input
          type="text"
          name="title"
          id="tc_title"
          class="input"
          placeholder="Contoh: Validasi Login User"
          required>
      </div>

      {{-- Status --}}
      <div class="form-group">
        <label>Status</label>
        <select name="status" id="tc_status" class="input">
          <option>Planned</option>
          <option>In Progress</option>
          <option>Passed</option>
          <option>Failed</option>
        </select>
      </div>

      {{-- Waktu --}}
      <div class="form-group">
        <label>Waktu Pengerjaan</label>
        <div class="time-grid">
          <div>
            <small>Waktu Mulai</small>
            <input type="datetime-local"
                   name="start_at"
                   id="tc_start_at"
                   class="input">
          </div>
          <div>
            <small>Waktu Selesai</small>
            <input type="datetime-local"
                   name="end_at"
                   id="tc_end_at"
                   class="input">
          </div>
        </div>
      </div>

      {{-- Tester --}}
      <div class="form-group">
        <label>Nama Tester</label>
        <input
          type="text"
          name="tester"
          id="tc_tester"
          class="input"
          placeholder="Contoh: Romi putradewa">
      </div>

      <button class="submit">Simpan Test Case</button>
    </form>
  </div>
</div>

{{-- ================= CSS ================= --}}
<style>
:root{--brand:#F69220}
body.modal-open{overflow:hidden}

.testing-hero{
  background:var(--brand);
  padding:18px;
  border-radius:16px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  color:#fff;
  margin-bottom:16px;
}
.testing-hero__btn{
  background:#fff;
  color:var(--brand);
  border:none;
  padding:10px 18px;
  border-radius:999px;
  font-weight:800;
  cursor:pointer;
}

.card{
  background:#fff;
  border-radius:16px;
  padding:16px;
}
.card-title{font-weight:800;margin-bottom:12px}

.table{width:100%;border-collapse:collapse}
.table th,.table td{
  padding:12px;
  border-bottom:1px solid #eee;
}
.td-title{font-weight:600}

.status{
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
}
.status-in-progress{background:#ffedd5;color:#c2410c}
.status-passed{background:#dcfce7;color:#166534}

.progress-row{display:flex;align-items:center;gap:6px}
.progress-bar{
  width:70px;height:6px;
  background:#e5e7eb;
  border-radius:999px;
}
.progress-bar span{
  display:block;height:100%;
  background:var(--brand);
  border-radius:999px;
}

.actions{display:flex;gap:6px}
.btn-edit{background:#e0f2fe;border:1px solid #bae6fd;padding:6px 10px;border-radius:8px}
.btn-delete{background:#fee2e2;border:1px solid #fecaca;padding:6px 10px;border-radius:8px}

.flash-success{
  background:#ecfdf5;border:1px solid #a7f3d0;
  padding:12px;border-radius:12px;margin-bottom:12px
}

/* MODAL */
.modal{
  position:fixed;inset:0;display:none;z-index:9999;
}
.modal.show{display:flex}
.modal-backdrop{
  position:absolute;inset:0;background:rgba(0,0,0,.45);
}
.modal-panel{
  margin:auto;
  background:#fff;
  border-radius:16px;
  padding:20px;
  width:420px;
  z-index:2;
}
.modal-head{
  display:flex;justify-content:space-between;
  align-items:center;margin-bottom:12px;
}
.modal-head button{
  background:none;border:none;font-size:20px;cursor:pointer;
}

.form-group{
  display:flex;flex-direction:column;
  gap:6px;margin-bottom:14px;
}
.form-group label{font-weight:600;font-size:14px}
.form-group small{font-size:12px;color:#6b7280}

.input{
  width:100%;
  padding:12px 14px;
  border-radius:12px;
  border:1px solid #e5e7eb;
}
.input:focus{
  outline:none;
  border-color:var(--brand);
  box-shadow:0 0 0 2px rgba(246,146,32,.15);
}

.time-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
}

.submit{
  width:100%;
  background:var(--brand);
  color:#fff;
  border:none;
  padding:14px;
  border-radius:14px;
  font-weight:900;
  cursor:pointer;
}
</style>

{{-- ================= JS ================= --}}
<script>
const modal = document.getElementById('testCaseModal');
const body  = document.body;

openTestModal.onclick = () => {
  testCaseForm.reset();
  testCaseForm.action = "{{ route('projects.test-cases.store', $project->id) }}";
  formMethod.value = 'POST';
  modalTitle.innerText = 'Tambah Test Case';
  modal.classList.add('show');
  body.classList.add('modal-open');
};

closeTestModal.onclick =
modal.querySelector('.modal-backdrop').onclick = () => {
  modal.classList.remove('show');
  body.classList.remove('modal-open');
};

window.openEditTestCase = tc => {
  testCaseForm.action = `/test-cases/${tc.id}`;
  formMethod.value = 'PUT';
  modalTitle.innerText = 'Edit Test Case';

  tc_title.value = tc.title;
  tc_status.value = tc.status;
  tc_start_at.value = tc.start_at ? tc.start_at.replace(' ','T') : '';
  tc_end_at.value   = tc.end_at ? tc.end_at.replace(' ','T') : '';
  tc_tester.value   = tc.tester ?? '';

  modal.classList.add('show');
  body.classList.add('modal-open');
};
</script>
