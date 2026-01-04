@php
  $devs = $project->developments()->with('designSpec')->latest()->get();
@endphp

{{-- ================= HERO ================= --}}
<div class="testing-hero">
  <div class="testing-hero__title">
    Pantau Proses Development
  </div>
  <button class="testing-hero__btn" onclick="openAddModal()">
    Tambah Development
  </button>
</div>

@if(session('ok'))
  <div class="flash-ok">{{ session('ok') }}</div>
@endif

{{-- ================= TABLE ================= --}}
<div class="card" style="margin-top:16px;">
  <div class="card-title">Development Task</div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Design Spec</th>
          <th>PIC</th>
          <th>Status</th>
          <th>Mulai</th>
          <th>Selesai</th>
          <th style="width:160px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($devs as $i => $d)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $d->designSpec->artifact_name ?? '-' }}</td>
          <td>{{ $d->pic ?? '-' }}</td>
          <td>{{ $d->status }}</td>
          <td>{{ $d->start_date?->format('d M Y') ?? '-' }}</td>
          <td>{{ $d->end_date?->format('d M Y') ?? '-' }}</td>
          <td class="aksi">
            <button class="btn-edit"
              onclick="openEditModal(
                {{ $d->id }},
                {{ $d->design_spec_id }},
                '{{ $d->status }}',
                '{{ addslashes($d->pic) }}',
                '{{ optional($d->start_date)->format('Y-m-d') }}',
                '{{ optional($d->end_date)->format('Y-m-d') }}'
              )">
              Edit
            </button>

            <form method="POST"
              action="{{ route('developments.destroy',$d->id) }}"
              onsubmit="return confirm('Hapus development ini?')">
              @csrf @method('DELETE')
              <button class="btn-delete">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7">Belum ada development task.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ================= MODAL TAMBAH ================= --}}
<div class="tc-modal" id="addModal">
  <div class="tc-modal__backdrop" onclick="closeAddModal()"></div>
  <div class="tc-modal__panel">
    <div class="tc-modal__head">
      <div class="tc-modal__title">Tambah Development</div>
      <button onclick="closeAddModal()">✕</button>
    </div>

    <form method="POST"
      action="{{ route('projects.developments.store',$project->id) }}"
      class="tc-form">
      @csrf

      <select name="design_spec_id" class="tc-input" required>
        <option value="">— Pilih Design Spec —</option>
        @foreach($project->designSpecs as $ds)
          <option value="{{ $ds->id }}">{{ $ds->artifact_name }}</option>
        @endforeach
      </select>

      <input name="pic" class="tc-input" placeholder="PIC / Developer">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <input type="date" name="start_date" class="tc-input">
        <input type="date" name="end_date" class="tc-input">
      </div>

      <select name="status" class="tc-input">
        <option>In Progress</option>
        <option>Review</option>
        <option>Done</option>
      </select>

      <button class="tc-submit">Simpan Development</button>
    </form>
  </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="tc-modal" id="editModal">
  <div class="tc-modal__backdrop" onclick="closeEditModal()"></div>
  <div class="tc-modal__panel">
    <div class="tc-modal__head">
      <div class="tc-modal__title">Edit Development</div>
      <button onclick="closeEditModal()">✕</button>
    </div>

    <form method="POST" id="editForm" class="tc-form">
      @csrf @method('PUT')

      <select name="design_spec_id" id="e_design" class="tc-input">
        @foreach($project->designSpecs as $ds)
          <option value="{{ $ds->id }}">{{ $ds->artifact_name }}</option>
        @endforeach
      </select>

      <input name="pic" id="e_pic" class="tc-input">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <input type="date" name="start_date" id="e_start" class="tc-input">
        <input type="date" name="end_date" id="e_end" class="tc-input">
      </div>

      <select name="status" id="e_status" class="tc-input">
        <option>In Progress</option>
        <option>Review</option>
        <option>Done</option>
      </select>

      <button class="tc-submit">Update Development</button>
    </form>
  </div>
</div>

{{-- ================= STYLE ================= --}}
<style>
.testing-hero{
  background:#F69220;border-radius:16px;
  padding:18px;display:flex;justify-content:space-between;color:#fff
}
.testing-hero__btn{
  background:#fff;color:#F69220;
  border:none;padding:10px 18px;border-radius:999px;font-weight:800
}
.aksi{display:flex;gap:8px}
.btn-edit{
  background:#e0f2fe;border:1px solid #bae6fd;
  color:#075985;padding:6px 14px;border-radius:8px
}
.btn-delete{
  background:#fee2e2;border:1px solid #fecaca;
  color:#7f1d1d;padding:6px 14px;border-radius:8px
}
.tc-modal{position:fixed;inset:0;display:none;z-index:999}
.tc-modal.show{display:block}
.tc-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.45)}
.tc-modal__panel{
  position:absolute;top:50%;left:50%;
  transform:translate(-50%,-50%);
  background:#fff;padding:20px;border-radius:18px;width:600px
}
.tc-form{display:flex;flex-direction:column;gap:12px}
.tc-input{padding:12px;border-radius:14px;border:1px solid #ddd}
.tc-submit{
  background:#F69220;color:#fff;
  border:none;padding:14px;border-radius:14px;font-weight:900
}
.flash-ok{
  background:#ecfdf5;border:1px solid #a7f3d0;
  color:#065f46;padding:12px;border-radius:14px;margin-top:12px
}
</style>

{{-- ================= SCRIPT ================= --}}
<script>
function openAddModal(){addModal.classList.add('show')}
function closeAddModal(){addModal.classList.remove('show')}

function openEditModal(id,ds,status,pic,s,e){
  editForm.action=`/developments/${id}`;
  e_design.value=ds;
  e_status.value=status;
  e_pic.value=pic||'';
  e_start.value=s||'';
  e_end.value=e||'';
  editModal.classList.add('show');
}
function closeEditModal(){editModal.classList.remove('show')}
</script>
