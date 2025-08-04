@extends ('master')
@section('content')
<style>
    .timeline {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 0;
    }

    .timeline::after {
        content: '';
        position: absolute;
        width: 6px;
        background-color: #e9ecef;
        top: 0;
        bottom: 0;
        left: 50%;
        margin-left: -3px;
        border-radius: 3px;
    }

    .timeline-container {
        padding: 10px 40px;
        position: relative;
        background-color: inherit;
        width: 50%;
    }

    /* The circles on the timeline */
    .timeline-container::after {
        content: '';
        position: absolute;
        width: 25px;
        height: 25px;
        right: -12.5px;
        background-color: white;
        border: 4px solid #6f42c1; /* Bootstrap purple */
        top: 15px;
        border-radius: 50%;
        z-index: 1;
    }

    /* Place the container to the left */
    .timeline-left {
        left: 0;
    }

    /* Place the container to the right */
    .timeline-right {
        left: 50%;
    }

    /* Add arrows to the left container (pointing right) */
    .timeline-left::before {
        content: " ";
        height: 0;
        position: absolute;
        top: 22px;
        width: 0;
        z-index: 1;
        right: 30px;
        border: medium solid white;
        border-width: 10px 0 10px 10px;
        border-color: transparent transparent transparent white;
    }

    /* Add arrows to the right container (pointing left) */
    .timeline-right::before {
        content: " ";
        height: 0;
        position: absolute;
        top: 22px;
        width: 0;
        z-index: 1;
        left: 30px;
        border: medium solid white;
        border-width: 10px 10px 10px 0;
        border-color: transparent white transparent transparent;
    }

    /* Fix the circle for containers on the right side */
    .timeline-right::after {
        left: -12.5px;
    }

    /* The actual content */
    .timeline-content {
        padding: 20px 30px;
        background-color: white;
        position: relative;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .timeline-content img {
        max-width: 100%;
        border-radius: 6px;
        margin-top: 15px;
    }

    /* Media queries - Responsive layout */
    @media screen and (max-width: 768px) {
        .timeline::after { left: 31px; }
        .timeline-container { width: 100%; padding-left: 70px; padding-right: 25px; }
        .timeline-container::before { left: 60px; border: medium solid white; border-width: 10px 10px 10px 0; border-color: transparent white transparent transparent; }
        .timeline-left::after, .timeline-right::after { left: 18.5px; }
        .timeline-left, .timeline-right { left: 0%; }
    }
</style>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bolder"><span class="text-gradient d-inline">Panduan Membuat Jersey</span></h2>
        <p class="lead">Ikuti 7 langkah mudah ini untuk mendapatkan jersey impian Anda.</p>
    </div>

    @php
    $steps = [
        ['title' => 'Mulai Desain', 'description' => 'Tekan tombol "Buat Jersey" di halaman utama untuk memulai petualangan kreatif Anda.', 'image' => 'assets/step1.png'],
        ['title' => 'Pilih Template', 'description' => 'Pilih salah satu template jersey yang paling sesuai dengan gaya tim Anda.', 'image' => 'assets/step2.png'],
        ['title' => 'Kreasikan Desain', 'description' => 'Bebaskan imajinasi Anda dengan menambahkan teks, logo, atau gambar pada jersey.', 'image' => 'assets/step3.png'],
        ['title' => 'Tambah ke Keranjang', 'description' => 'Setelah puas dengan desain Anda, klik tombol untuk menambahkannya ke keranjang belanja.', 'image' => 'assets/step4.png'],
        ['title' => 'Isi Alamat', 'description' => 'Lengkapi data diri dan alamat pengiriman dengan benar agar jersey sampai tujuan.', 'image' => 'assets/step5.png'],
        ['title' => 'Lakukan Pembayaran', 'description' => 'Pilih metode pembayaran yang paling nyaman bagi Anda dan selesaikan transaksi.', 'image' => 'assets/step6.png'],
        ['title' => 'Pesanan Diproses', 'description' => 'Selamat! Pembayaran berhasil dan pesanan Anda akan segera kami proses.', 'image' => 'assets/step7.png'],
    ];
    @endphp

    <div class="timeline">
        @foreach($steps as $index => $step)
            <div class="timeline-container {{ $index % 2 == 0 ? 'timeline-left' : 'timeline-right' }}">
                <div class="timeline-content">
                    <h4 class="fw-bolder text-gradient">{{ $index + 1 }}. {{ $step['title'] }}</h4>
                    <p>{{ $step['description'] }}</p>
                    <img src="{{ asset($step['image']) }}" alt="Langkah {{ $index + 1 }}">
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection