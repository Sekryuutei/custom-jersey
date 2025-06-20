@extends ('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h2 class="display-5 fw-bolder text-center"><span class="text-gradient d-inline">Panduan</span></h2><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Pertama tekan tombol buat jersey</p>
    <img src="{{ asset('assets/step1.png') }}" alt="Step 1" style="width: 400px; height: auto;"><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Kedua pilih jersey</p>
    <img src="{{ asset('assets/step2.png') }}" alt="Step 2" style="width: 400px; height: auto;"><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Ketiga desain jersey</p>
    <img src="{{ asset('assets/step3.png') }}" alt="Step 3" style="width: 400px; height: auto;"><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Keempat tekan tombol beli jersey</p>
    <img src="{{ asset('assets/step4.png') }}" alt="Step 4" style="width: 400px; height: auto;"><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Kelima isi identitas untuk pengiriman jersey</p>
    <img src="{{ asset('assets/step5.png') }}" alt="Step 5" style="width: 400px; height: auto;"><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Keenam bayar sesuai metode yang diinginkan</p>
    <img src="{{ asset('assets/step6.png') }}" alt="Step 6" style="width: 400px; height: auto;"><br>
    <p style="font-family: Arial, Helvetica, sans-serif; font-size: larger;">Terakhir pembayaran berhasil</p>
    <img src="{{ asset('assets/step7.png') }}" alt="Step 7" style="width: 400px; height: auto;"><br>
</div>
@endsection