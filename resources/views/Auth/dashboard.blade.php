@extends('layouts.app')
@section('title','Dashboard')

@section('content')

{{-- ================= HERO ================= --}}
<section class="hero">
    <div class="hero-left">
        <p class="subtitle">Welcome To Tracking</p>
        <h1 class="title">{{ $stats['user_name'] }}</h1>
    </div>

    {{-- Ilustrasi kanan --}}
    <div class="hero-right">
        <img
            src="{{ asset('images/dashboard-background.png') }}"
            alt="Dashboard background"
            class="hero-illustration"
            loading="lazy">
    </div>
</section>

{{-- ================= METRIC CARDS ================= --}}
<section class="cards">
    <div class="card">
        <div class="card-title">Total Project</div>
        <div class="card-value">{{ $stats['total_project'] }}</div>
    </div>

    <div class="card">
        <div class="card-title">In Progress</div>
        <div class="card-value">{{ $stats['total_in_progress'] }}</div>
    </div>

    <div class="card">
        <div class="card-title">Review</div>
        <div class="card-value">{{ $stats['total_review'] }}</div>
    </div>

    <div class="card">
        <div class="card-title">Selesai</div>
        <div class="card-value">{{ $stats['total_done'] }}</div>
    </div>
</section>

@endsection
