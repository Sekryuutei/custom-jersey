@extends ('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Terima Kasih!</span></h3>
  
        @if($payment->file_name)
        <img src="{{ $payment->file_name }}" alt="Design" style="width: 500px; height: auto;">
        @else
        No Design
        @endif
    <p class="lead text-center" style="font-family:Sans-serif; font-size: larger;">Untuk {{$payment->name}}</p>
    <p class="text-center" style="font-family:Sans-serif; font-size: large;">Pesanan anda dengan id ({{$payment->id}}) telah diproses mohon ditunggu akan kami kirim paling lambat selama 3 hari</p>

</div>

@endsection