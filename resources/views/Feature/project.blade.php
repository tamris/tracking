@extends('layouts.app')
@section('title','Project')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/projects.css') }}">
<style>
  .link-project { color:#0b1727; text-decoration:none }
  .link-project:hover { text-decoration:underline }
</style>
@endpush

@section('content')

{{-- ================= ALERT ================= --}}
@if (session('success'))
  <div class="panel alert success">
    <div class="alert-title">Berhasil</div>
    <div>{{ session('success') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="panel alert danger">
    <div class="alert-title">Gagal menyimpan</div>
    <ul>
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

{{-- ================= HERO ================= --}}
<section class="project-hero">
  <div class="left">
    <h1 class="title">Project</h1>
    <p class="subtitle">
      Pantau dan kelola seluruh project kamu di sini.
    </p>
  </div>
  <div class="right">
    <button
      class="btn btn-brand"
      type="button"
      id="projectModal">
      Tambah Project
    </button>
  </div>
</section>

{{-- ================= TABLE ================= --}}
<div class="panel table-panel">
  <div class="proj-table">

    {{-- TABLE HEADER --}}
    <div class="proj-head">
      <div class="th">Tugas</div>
      <div class="th">PIC</div>
      <div class="th">Status</div>
      <div class="th">Tanggal Mulai</div>
      <div class="th">Tanggal Selesai</div>
      <div class="th">Persentase</div>
      <div class="th">Dokumen Kontrak</div>
      <div class="th">Kegiatan</div>
      <div class="th w-actions">Aksi</div>
    </div>

    {{-- TABLE BODY --}}
    <div class="proj-body">
      @forelse ($projects as $p)
        @php
          $map = [
            'todo'        => ['Belum Mulai', '#64748b'],
            'in_progress' => ['In Progress', '#f59e0b'],
            'review'      => ['Review', '#3b82f6'],
            'done'        => ['Selesai', '#22c55e'],
          ];
          [$label,$color] = $map[$p->status] ?? ['-', '#94a3b8'];

          $contractUrl = $p->contract_file
            ? asset('storage/'.$p->contract_file)
            : null;
        @endphp

        <div class="proj-row">
          <div class="cell">
            <a class="link-project"
               href="{{ route('projects.show', $p->id) }}">
              <b>{{ $p->title }}</b>
            </a>
          </div>

          <div class="cell">{{ $p->pic ?: '-' }}</div>

          <div class="cell">
            <span class="pill">
              <span class="dot" style="background:{{ $color }}"></span>
              {{ $label }}
            </span>
          </div>

          <div class="cell">
            {{ $p->start_date?->translatedFormat('d M Y') ?: '-' }}
          </div>

          <div class="cell">
            {{ $p->end_date?->translatedFormat('d M Y') ?: '-' }}
          </div>

         <div class="cell">
            <div class="meter">
              {{-- Ganti $p->progress menjadi $p->overall_progress --}}
              <span style="width:{{ (int)($p->overall_progress ?? 0) }}%"></span>
            </div>
            <small>{{ (int)($p->overall_progress ?? 0) }}%</small>
          </div>

          <div class="cell">
            @if($contractUrl)
              <a class="link-project"
                 href="{{ $contractUrl }}"
                 target="_blank"
                 rel="noopener">
                Lihat
              </a>
            @else
              -
            @endif
          </div>

          <div class="cell">{{ $p->activity ?: '-' }}</div>

          <div class="cell actions">

            {{-- EDIT --}}
            <button
              type="button"
              class="btn btn-ghost sm btn-edit"
              data-id="{{ $p->id }}"
              data-title="{{ $p->title }}"
              data-pic="{{ $p->pic }}"
              data-status="{{ $p->status }}"
              data-start="{{ $p->start_date?->format('Y-m-d') }}"
              data-end="{{ $p->end_date?->format('Y-m-d') }}"
              data-progress="{{ (int)($p->progress ?? 0) }}"
              data-activity="{{ $p->activity }}">
              ✎
            </button>

            {{-- DELETE --}}
            <form id="del-{{ $p->id }}"
                  class="hidden"
                  method="POST"
                  action="{{ route('projects.destroy',$p->id) }}">
              @csrf
              @method('DELETE')
            </form>

            <button
              type="button"
              class="btn btn-ghost sm btn-del"
              data-id="{{ $p->id }}">
              🗑
            </button>
          </div>
        </div>
      @empty
        <div class="proj-row is-empty">
          <div class="cell cell-full">
            <div class="empty-card">
              <div class="empty-icon">📋</div>
              <div class="empty-title">Belum ada project</div>
              <div class="empty-desc">
                Klik <b>Tambah Project</b> untuk membuat project pertama.
              </div>
            </div>
          </div>
        </div>
      @endforelse
    </div>

  </div>
</div>

{{-- ================= MODAL CREATE / EDIT ================= --}}
{{-- MODAL TIDAK DIUBAH, TETAP PAKAI YANG KAMU PUNYA --}}
@includeIf('Feature.project-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. IDENTIFIKASI MODAL (Sesuaikan ID 'modalProject' dengan ID di file project-modal.blade.php)
    const modalElement = document.getElementById('modalProject'); 
    
    // Fungsi pembantu untuk menampilkan/menyembunyikan modal
    const toggleModal = (show = true) => {
        if (!modalElement) return;
        if (show) {
            modalElement.style.display = 'flex'; // Atau 'block' tergantung CSS kamu
            modalElement.classList.add('show');
        } else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
        }
    };

    // 2. TOMBOL TAMBAH
    const btnTambah = document.getElementById('projectModal');
    if (btnTambah) {
        btnTambah.addEventListener('click', function() {
            const form = modalElement.querySelector('form');
            form.action = "{{ route('projects.store') }}";
            
            // Hapus input _method PUT jika ada (bekas edit)
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();
            
            form.reset(); // Kosongkan form
            modalElement.querySelector('.modal-title').innerText = 'Tambah Project';
            toggleModal(true);
        });
    }

    // 3. TOMBOL EDIT
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = modalElement.querySelector('form');

            // Update URL action form ke route update project tersebut
            form.action = `/projects/${id}`;
            
            // Pastikan ada input _method "PUT" untuk Laravel update
            if(!form.querySelector('input[name="_method"]')) {
                let methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);
            }

            // Memasukkan data dari tombol ke input modal secara presisi
            form.querySelector('input[name="title"]').value = this.dataset.title || '';
            form.querySelector('input[name="pic"]').value = this.dataset.pic || '';
            form.querySelector('select[name="status"]').value = this.dataset.status || 'todo';
            form.querySelector('input[name="start_date"]').value = this.dataset.start || '';
            form.querySelector('input[name="end_date"]').value = this.dataset.end || '';
            // form.querySelector('input[name="progress"]').value = this.dataset.progress || 0;
            
            // Jika ada input untuk kegiatan (activity)
            const activityInput = form.querySelector('input[name="activity"]');
            if (activityInput) activityInput.value = this.dataset.activity || '';

            // Ubah judul modal menjadi Edit
            modalElement.querySelector('.modal-title').innerText = 'Edit Project';
            
            // Tampilkan modal
            toggleModal(true);
        });
    });

    // 4. TOMBOL HAPUS (Sudah berfungsi dengan konfirmasi)
    document.querySelectorAll('.btn-del').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('Apakah Anda yakin ingin menghapus project ini?')) {
                document.getElementById('del-' + id).submit(); // Kirim form delete tersembunyi
            }
        });
    });

    // 5. TOMBOL CLOSE (Klik di luar modal untuk tutup)
    window.onclick = function(event) {
        if (event.target == modalElement) {
            toggleModal(false);
        }
    };
});
</script>
@endpush

