@extends ('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Terima Kasih!</span></h3>
  
        @if($payment->file_name)
        <img src="{{ $payment->file_name }}" alt="Design" style="width: 500px; height: auto;">
        @else
        No Design
        @endif
    <p class="lead text-center">Untuk {{$payment->name}}</p>
    <p class="text-center">Pesanan anda sedang diproses mohon ditunggu akan kami kirim paling lambat selama 3 hari</p>
        
    <!-- <ul>
        <li><strong>ID:</strong>{{ $payment->id }}</li>
        <li><strong>Name:</strong>{{ $payment->name }}</li>
        <li><strong>Email:</strong>{{ $payment->email }}</li>
        <li><strong>Phone:</strong>{{ $payment->phone }}</li>
        <li><strong>Address:</strong>{{ $payment->address }}</li>
        <li><strong>Amount:</strong>{{ $payment->amount }}</li>
        <li><strong>Price:</strong>{{ $payment->price }}</li>
        <li><strong>Status:</strong>{{ $payment->status }}</li>
    </ul> -->

</div>

@endsection