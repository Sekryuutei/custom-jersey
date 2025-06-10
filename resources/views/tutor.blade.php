@extends ('master')
@section('content')
<div>
    <h2 class="display-5 fw-bolder justify-center"><span class="text-gradient d-inline">Langkah Membuat Jersey</span></h2>
    <p>Pertama Tekan Tombol Buat Jersey</p>
    <img src="{{ asset('assets/step1.png') }}" alt="Step 1" style="width: 100px; height: auto;"><br>
    <p>Kedua Pilih Jersey</p>
    <img src="{{ asset('assets/step2.png') }}" alt="Step 2" style="width: 100px; height: auto;"><br>
    <p>Ketiga Desain Jersey</p>
    <img src="{{ asset('assets/step3.png') }}" alt="Step 3" style="width: 100px; height: auto;"><br>
    <p>Keempat Beli Jersey</p>
    <img src="{{ asset('assets/step4.png') }}" alt="Step 4" style="width: 100px; height: auto;"><br>
    <p>Terakhir Cek Pesanan</p>
    <img src="{{ asset('assets/step5.png') }}" alt="Step 5" style="width: 100px; height: auto;"><br>
</div>
@endsection