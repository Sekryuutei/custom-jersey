@extends ('master')
@section('content')
<div class="container px-4 my-5">
    <div class="text-center">
        <h2 class="display-5 fw-bolder"><span class="text-gradient d-inline">Panduan</span></h2><br>
    </div>
    <div class="row gx-5 justify-content-center">
        <div class="col-lg-8 text-center">
            <p class="fs-5 mb-4">1. Pertama, tekan tombol "Buat Jersey" di halaman utama.</p>
            <img src="{{ asset('assets/step1.png') }}" alt="Step 1" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
            <p class="fs-5 mb-4">2. Kedua, pilih template jersey yang Anda inginkan.</p>
            <img src="{{ asset('assets/step2.png') }}" alt="Step 2" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
            <p class="fs-5 mb-4">3. Ketiga, kreasikan desain Anda dengan menambahkan teks atau gambar.</p>
            <img src="{{ asset('assets/step3.png') }}" alt="Step 3" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
            <p class="fs-5 mb-4">4. Keempat, setelah selesai, tambahkan desain ke keranjang.</p>
            <img src="{{ asset('assets/step4.png') }}" alt="Step 4" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
            <p class="fs-5 mb-4">5. Kelima, isi identitas dan alamat untuk pengiriman jersey.</p>
            <img src="{{ asset('assets/step5.png') }}" alt="Step 5" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
            <p class="fs-5 mb-4">6. Keenam, bayar sesuai metode yang diinginkan.</p>
            <img src="{{ asset('assets/step6.png') }}" alt="Step 6" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
            <p class="fs-5 mb-4">7. Terakhir, pembayaran berhasil dan pesanan Anda akan kami proses!</p>
            <img src="{{ asset('assets/step7.png') }}" alt="Step 7" class="img-fluid rounded shadow-sm mb-5" style="max-width: 500px;"><br>
        </div>
    </div>
</div>
@endsection