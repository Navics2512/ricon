@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
    /* ===================== */
    /* TYPOGRAPHY */
    /* ===================== */

    .hero h1 {
        font-size: 40px;
        font-weight: 800;
    }

    .hero p {
        font-size: 18px;
        opacity: 0.9;
    }

    .card h5 {
        color: #1f2f5c;
        font-weight: 700;
    }

    .locker-number {
        font-size: 48px;
        font-weight: 800;
        color: #1f2f5c;
    }

    /* ===================== */
    /* BUTTONS */
    /* ===================== */

    .btn-rounded {
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 15px;
    }

    /* navy button (Add item, show QR, assign) */
    .btn-navy {
        background: #1f2f5c;
        color: #fff;
        border: none;
    }

    .btn-navy:hover {
        background: #253b73;
        color: #fff;
    }

    /* gradient primary button */
    .btn-gradient {
        background: linear-gradient(90deg, #1f3c88 0%, #2f8f9d 100%);
        color: #fff;
        border: none;
    }

    .btn-gradient:hover {
        background: linear-gradient(90deg, #1f3c88 0%, #2f8f9d 100%);
        color: #fff !important;
    }

    /* release locker */
    .btn-release {
        background: #43b36b;
        color: #fff;
        border: none;
    }

    .btn-release:hover {
        background: #3aa160;
        color: #fff;
    }

    /* STOP bootstrap active transparency */
    .btn:active {
        opacity: 1 !important;
        transform: none !important;
    }

    .btn.btn-navy:active,
    .btn.btn-navy.active,
    .btn.btn-navy:focus,
    .btn.btn-navy:focus-visible {
        background-color: #1f2f5c !important;
        color: #ffffff !important;
        box-shadow: none !important;
         outline: none !important;
         transform: none;
    }

    .btn.btn-release:active,
    .btn.btn-release.active,
    .btn.btn-release:focus,
    .btn.btn-release:focus-visible {
        background-color: #3aa160 !important;
        color: #ffffff !important;
        box-shadow: none !important;
         outline: none !important;
         transform: none;
    }
    </style>
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="autoDismissAlert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{-- HERO --}}
    <div class="hero d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold text-white">Hi, {{ Auth::check() ? Auth::user()->name : 'Guest' }}</h1>
            <p class="fs-5 fst-italic text-white">pesan loker mu sekarang juga!</p>
        </div>

    @if ($booking)
        <button class="btn btn-secondary btn-rounded px-4 btn-gradient" disabled>
            Pesan Loker
        </button>
    @else
        <a href="{{ route('booking.index') }}" class="btn btn-gradient btn-rounded px-4">
            Pesan Loker
        </a>
    @endif

    </div>

    {{-- MAIN CONTENT --}}
    <div class="row g-4 mt-3">

        {{-- ACTIVE BOOKINGS --}}
        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">Pesanan Aktif:</h5>
                @if ($booking)
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <p class="mb-1 fw-semibold">nomor loker:</p>
                            <div class="locker-number">1</div>
                        </div>

                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="{{ route('booking.edit', $booking->id) }}"
                                        class="btn btn-navy w-100 btn-rounded">
                                        Tambah Barang
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-navy w-100 btn-rounded">
                                        Tampilkan Kode QR
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('booking.showAssignUserForm', $booking) }}" class="btn btn-navy w-100 btn-rounded">
                                        Tugaskan Pengambil Barang
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <form action="{{ route('booking.release', $booking) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-release w-100 btn-rounded">
                                            Lepaskan Loker
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        Anda tidak memiliki pemesanan aktif.
                    </div>
                @endif
            </div>
        </div>

        {{-- LOCKERS AVAILABLE --}}
        <div class="col-lg-4">
            <div class="card p-4 text-center">
                <h5 class="fw-bold">Loker Tersedia:</h5>
                <div class="locker-number mt-3">{{ $availableLockerCount }}</div>
            </div>
        </div>

        {{-- BOOK A LOCKER CARD --}}
        {{-- <div class="col-lg-6">
            <div class="card p-4 text-center">
                <h4 class="fw-bold mb-3">Pesan Loker</h4>
                <img src="https://cdn-icons-png.flaticon.com/512/3050/3050525.png" alt="Locker" style="max-width:200px"
                    class="mx-auto">
            </div>
        </div> --}}

        {{-- EMPTY / FUTURE CARD --}}
        {{-- <div class="col-lg-6">
            <div class="card p-4" style="min-height:240px;">
            </div>
        </div> --}}

    </div>

@endsection

<style>
    .btn-gradient {
        background: linear-gradient(90deg, #1f3c88 0%, #2f8f9d 100%);
        color: #ffffff;
        border: none;
        border-radius: 18px;
        padding: 14px 32px;
        font-size: 20px;
        font-weight: 600;
        /* width: 100%; */
        transition: all 0.25s ease;
    }

    .btn-gradient:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
    }

    .btn-gradient:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
</style>


