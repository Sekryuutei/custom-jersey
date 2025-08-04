@extends('master')
@section('content')
<div class="container my-5">
    <h3 class="display-5 fw-bolder text-center"><span class="text-gradient d-inline">Riwayat Pesanan Saya</span></h3>

    @forelse($orders as $order)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Pesanan #{{ $order->order_id }}</strong><br>
                    <small class="text-muted">Tanggal: {{ $order->created_at->format('d F Y') }}</small>
                </div>
                @php
                    $statusClass = '';
                    $statusText = '';
                    switch ($order->status) {
                        case 'success':
                        case 'settlement':
                            $statusClass = 'bg-success';
                            $statusText = 'Lunas';
                            break;
                        case 'pending':
                            $statusClass = 'bg-warning text-dark';
                            $statusText = 'Menunggu Pembayaran';
                            break;
                        case 'failed':
                        case 'expire':
                        case 'cancel':
                            $statusClass = 'bg-danger';
                            $statusText = 'Gagal';
                            break;
                        default:
                            $statusClass = 'bg-secondary';
                            $statusText = ucfirst($order->status);
                    }
                @endphp
                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @foreach($order->orderItems as $item)
                        <li class="list-group-item d-flex align-items-center">
                            <img src="{{ $item->file_name }}" alt="Design" class="me-3" style="width: 60px; height: auto;">
                            <div>
                                Jersey Kustom (Ukuran: {{ $item->size }})
                                <br>
                                <small>{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</small>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer text-end">
                <strong>Total: Rp {{ number_format($order->amount, 0, ',', '.') }}</strong>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <p class="lead">Anda belum memiliki riwayat pesanan.</p>
            <a href="{{ route('templates.index') }}" class="btn btn-primary">Mulai Berbelanja</a>
        </div>
    @endforelse

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>
@endsection
