@extends('layouts.app')

@section('title','Contact')

@push('styles')
  @vite('resources/css/contact.css')
@endpush

@section('content')
<section class="contact-page">

  {{-- Alert sukses --}}
  @if(session('success'))
      <div class="alert alert-success">
          {{ session('success') }}
      </div>
  @endif

  {{-- Validasi error --}}
  @if($errors->any())
      <div class="alert alert-error">
          <ul>
              @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif

  {{-- Breadcrumb --}}
  <nav class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Contact</span>
  </nav>

  {{-- Hero --}}
  <header class="contact-hero card">
    <div class="hero-text">
      <p class="eyebrow">Butuh Bantuan?</p>
      <h1 class="title">Hubungi Kami ✉️</h1>
      <p class="subtitle">
        Sampaikan pertanyaan, kritik, atau saran terkait aplikasi <strong>Project Tracking</strong>.
        Kami akan berusaha merespon secepat mungkin.
      </p>

      <div class="hero-badges">
        <span class="badge">Respon cepat</span>
        <span class="badge badge-soft">Jam kerja 09.00–17.00</span>
      </div>
    </div>

    <div class="hero-cta">
      <p class="cta-label">Ikuti kami di Instagram</p>

      {{-- BUTTON INSTAGRAM DENGAN PNG --}}
      <a  class="btn-instagram"
          href="https://www.instagram.com/iotanesia?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
          target="_blank"
          rel="noopener noreferrer">
        <span class="icon">
          <img src="{{ asset('images/instagram.png') }}" alt="Instagram">
        </span>
        <span class="ig-text">
          <strong>@Iotanesia</strong>
          <small>Lihat update & progress terbaru</small>
        </span>
      </a>

      <p class="cta-hint">
        Klik tombol di atas untuk langsung membuka profil Instagram kami.
      </p>
    </div>
  </header>

  {{-- Content 2 kolom --}}
  <div class="contact-grid">
    {{-- Info Kontak --}}
    <section class="card info-card">
      <h2>Informasi Kontak</h2>
      <p class="lead">
        Kamu bisa menghubungi kami melalui email atau WhatsApp.
      </p>

      <ul class="info-list">
        <li>
          <span class="icon">📧</span>
          <div>
            <p class="label">Email</p>
            <p class="value">support@projecttracking.test</p>
          </div>
        </li>
        <li>
          <span class="icon">📱</span>
          <div>
            <p class="label">WhatsApp</p>
            <p class="value">+62 812-3456-7890</p>
          </div>
        </li>
      </ul>

      <div class="hint-box">
        <p>
          <strong>Catatan:</strong> Balasan mungkin sedikit lebih lama di luar jam kerja
          atau saat banyak permintaan masuk.
        </p>
      </div>
    </section>

    {{-- Form Kontak --}}
    <section class="card form-card">
      <h2>Kirim Pesan</h2>
      <p class="lead">
        Kirimkan pesanmu lewat form ini. Pesan akan diteruskan ke tim kami.
      </p>

      <form action="{{ route('contact.store') }}" method="POST" class="contact-form">
        @csrf

        <div class="field">
          <label for="name">Nama Lengkap</label>
          <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name') }}"
            placeholder="Masukkan nama kamu">
        </div>

        <div class="field-grid">
          <div class="field">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              value="{{ old('email') }}"
              placeholder="nama@email.com">
          </div>
          <div class="field">
            <label for="topic">Topik</label>
            <select id="topic" name="topic">
              <option value="" disabled {{ old('topic') ? '' : 'selected' }}>Pilih topik</option>
              <option value="Bug / Error"      {{ old('topic') == 'Bug / Error' ? 'selected' : '' }}>Bug / Error</option>
              <option value="Permintaan Fitur" {{ old('topic') == 'Permintaan Fitur' ? 'selected' : '' }}>Permintaan Fitur</option>
              <option value="Masukan Desain"   {{ old('topic') == 'Masukan Desain' ? 'selected' : '' }}>Masukan Desain</option>
              <option value="Lainnya"          {{ old('topic') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="message">Pesan</label>
          <textarea
            id="message"
            name="message"
            rows="4"
            placeholder="Tulis pesanmu di sini">{{ old('message') }}</textarea>
        </div>

        <div class="form-footer">
          <p class="note">
            Dengan mengirim pesan, kamu setuju bahwa data ini digunakan untuk menindaklanjuti pertanyaanmu.
          </p>
          <button type="submit" class="btn-submit">
            Kirim Pesan
          </button>
        </div>
      </form>
    </section>
  </div>

</section>
@endsection
