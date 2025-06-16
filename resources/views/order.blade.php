@extends ('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Design Your Jersey</span></h3>
  
        @if($payment->file_name)
        <img src="{{ $payment->file_name }}" alt="Design" style="width: 100px; height: auto;">
        @else
        No Design
        @endif
        
    <ul>
        <li><strong>ID:</strong>{{ $payment->id }}</li>
        <li><strong>Name:</strong>{{ $payment->name }}</li>
        <li><strong>Email:</strong>{{ $payment->email }}</li>
        <li><strong>Phone:</strong>{{ $payment->phone }}</li>
        <li><strong>Address:</strong>{{ $payment->address }}</li>
        <li><strong>Amount:</strong>{{ $payment->amount }}</li>
        <li><strong>Price:</strong>{{ $payment->price }}</li>
        <li><strong>Status:</strong>{{ $payment->status }}</li>
    </ul>

</div>

@endsection