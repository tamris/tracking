@php use Illuminate\Support\Str; @endphp

<div class="card">

  {{-- ================= HEADER ================= --}}
  <div class="card-header">
    <h3>Design Specification</h3>
    <button class="btn-add" onclick="openAddModal()">Tambah Design</button>
  </div>

  {{-- ALERT --}}
  @if(session('ok'))
    <div class="alert success">{{ session('ok') }}</div>
  @endif

  {{-- ================= TABLE ================= --}}
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Requirement</th>
          <th>Tipe</th>
          <th>Artefak</th>
          <th>PIC</th>
          <th>Status</th>
          <th>Mulai</th>
          <th>Selesai</th>
          <th style="width:150px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($project->designSpecs as $i => $d)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ optional($d->requirement)->title ?? '-' }}</td>
          <td>{{ $d->artifact_type }}</td>
          <td>{{ $d->artifact_name }}</td>
          <td>{{ $d->pic ?? '-' }}</td>
          <td>{{ $d->status }}</td>
          <td>{{ $d->start_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $d->end_date?->format('d M Y') ?? '-' }}</td>
          <td class="aksi">
            <button class="btn-edit"
              onclick="openEditModal(
                {{ $d->id }},
                {{ $d->requirement_id }},
                '{{ $d->artifact_type }}',
                '{{ addslashes($d->artifact_name) }}',
                '{{ $d->status }}',
                '{{ addslashes($d->pic) }}',
                '{{ optional($d->start_date)->format('Y-m-d') }}',
                '{{ optional($d->end_date)->format('Y-m-d') }}',
                `{{ $d->rationale }}`
              )">
              Edit
            </button>

            <form method="POST"
              action="{{ route('design-specs.destroy', $d->id) }}"
              onsubmit="return confirm('Hapus design ini?')">
              @csrf @method('DELETE')
              <button class="btn-delete">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="9" class="muted">Belum ada design specification</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ================= MODAL TAMBAH ================= --}}
<div class="modal" id="addModal">
  <div class="modal-box">

    <div class="modal-header">
      <h3>Tambah Design Specification</h3>
      <button onclick="closeAddModal()">✕</button>
    </div>

    <form method="POST"
          action="{{ route('projects.design-specs.store', $project->id) }}"
          class="form-stack">
      @csrf

      <label>Requirement</label>
      <select class="i" name="requirement_id" required>
        <option value="">— Pilih Requirement —</option>
        @foreach($project->requirements as $r)
          <option value="{{ $r->id }}">{{ $r->title }}</option>
        @endforeach
      </select>

      <div class="grid-3">
        <select class="i" name="artifact_type">
          @foreach(['UI','API','DB','Flow'] as $t)
            <option>{{ $t }}</option>
          @endforeach
        </select>

        <input class="i" name="artifact_name"
               placeholder="Nama artefak" required>

        <select class="i" name="status">
          @foreach(['Draft','Review','Approved'] as $s)
            <option>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <input class="i" name="pic" placeholder="PIC / Penanggung jawab">

      <div class="grid-2">
        <input class="i" type="date" name="start_date">
        <input class="i" type="date" name="end_date">
      </div>

      <textarea class="i" name="rationale"
        rows="4" placeholder="Catatan / rationale (opsional)"></textarea>

      <button class="btn-submit">Simpan Design</button>
    </form>
  </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal" id="editModal">
  <div class="modal-box">

    <div class="modal-header">
      <h3>Edit Design Specification</h3>
      <button onclick="closeEditModal()">✕</button>
    </div>

    <form method="POST" id="editForm" class="form-stack">
      @csrf @method('PUT')

      <select class="i" name="requirement_id" id="e_requirement"></select>

      <div class="grid-3">
        <select class="i" name="artifact_type" id="e_type">
          @foreach(['UI','API','DB','Flow'] as $t)
            <option>{{ $t }}</option>
          @endforeach
        </select>

        <input class="i" name="artifact_name" id="e_name">

        <select class="i" name="status" id="e_status">
          @foreach(['Draft','Review','Approved'] as $s)
            <option>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <input class="i" name="pic" id="e_pic">

      <div class="grid-2">
        <input class="i" type="date" name="start_date" id="e_start">
        <input class="i" type="date" name="end_date" id="e_end">
      </div>

      <textarea class="i" name="rationale" id="e_rationale" rows="4"></textarea>

      <button class="btn-submit">Simpan Perubahan</button>
    </form>
  </div>
</div>

{{-- ================= CSS ================= --}}
<style>
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.btn-add{background:#ff9800;color:#fff;border:none;padding:10px 20px;border-radius:999px;font-weight:600}
.aksi{display:flex;gap:8px}
.btn-edit{background:#e8f3ff;border:1px solid #b6dcff;padding:6px 12px;border-radius:8px}
.btn-delete{background:#ffeaea;border:1px solid #ffbcbc;padding:6px 12px;border-radius:8px;color:#b91c1c}

.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);justify-content:center;align-items:center;z-index:50}
.modal-box{background:#fff;width:600px;max-width:95%;padding:24px;border-radius:18px}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}

.form-stack{display:flex;flex-direction:column;gap:12px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.grid-3{display:grid;grid-template-columns:1fr 1.4fr 1fr;gap:12px}

.i{padding:10px 12px;border-radius:12px;border:1px solid #e5e7eb}
.i:focus{border-color:#ff9800;outline:none}
.btn-submit{background:linear-gradient(90deg,#ff9800,#ffa726);border:none;color:#fff;padding:14px;border-radius:999px;font-weight:600}
</style>

{{-- ================= JS ================= --}}
<script>
function openAddModal(){addModal.style.display='flex'}
function closeAddModal(){addModal.style.display='none'}
function openEditModal(id,req,type,name,status,pic,start,end,rat){
  editForm.action=`/design-specs/${id}`
  e_requirement.innerHTML=document.querySelector('select[name=requirement_id]').innerHTML
  e_requirement.value=req
  e_type.value=type
  e_name.value=name
  e_status.value=status
  e_pic.value=pic||''
  e_start.value=start||''
  e_end.value=end||''
  e_rationale.value=rat||''
  editModal.style.display='flex'
}
function closeEditModal(){editModal.style.display='none'}
</script>
