@extends ('master')
@section('content')
    <div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
        <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Admin Page</span></h3>
        <div class="container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Design</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->name }}</td>
                            <td>{{ $payment->email }}</td>
                            <td>{{ $payment->phone }}</td>
                            <td>{{ $payment->address }}</td>
                            <td>{{ $payment->price }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>
                                @if($payment->file_name)
                                    <img src="{{ $payment->file_name }}" alt="Design" style="width: 100px; height: auto;">
                                @else
                                    No Design
                                @endif
                            </td>
                            <td>
                                @if($payment->file_name)
                                    <a href="{{ route('payment.download', $payment->id) }}" class="btn btn-primary">Download</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@ends


@endsection