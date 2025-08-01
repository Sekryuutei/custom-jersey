@extends('master')
@section('content')
<div class="container my-5">
    <h3 class="display-6 fw-bolder mb-4"><span class="text-gradient d-inline">Detail Pesanan #{{ $payment->order_id }}</span></h3>

    <div class="row">
        <!-- Customer Details -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Informasi Pelanggan</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong><br>{{ $payment->name }}</p>
                    <p><strong>Email:</strong><br>{{ $payment->email }}</p>
                    <p><strong>No. HP:</strong><br>{{ $payment->phone }}</p>
                    <p><strong>Alamat:</strong><br>{{ $payment->address }}</p>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Desain</th>
                                    <th>Ukuran</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->orderItems as $item)
                                <tr>
                                    <td>
                                        <a href="{{ $item->file_name }}" target="_blank">
                                            <img src="{{ $item->file_name }}" alt="Design" style="width: 80px; height: auto;">
                                        </a>
                                    </td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Kembali ke Daftar Pesanan</a>
    </div>
</div>
@endsection
