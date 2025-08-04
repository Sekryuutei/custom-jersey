@extends('master')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h4 class="mb-0"><span class="text-gradient d-inline">Status Pesanan Anda</span></h4>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Order ID: <span class="text-gradient fw-bolder">#{{ $payment->order_id }}</span></h5>
                        @php
                            $statusClass = '';
                            $statusText = '';
                            switch ($payment->status) {
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
                                    $statusText = ucfirst($payment->status);
                            }
                        @endphp
                        <span class="badge fs-6 {{ $statusClass }}">{{ $statusText }}</span>
                    </div>

                    @if($payment->status == 'pending')
                        <div class="alert alert-warning" role="alert">
                            Silakan selesaikan pembayaran Anda. Jika Anda sudah membayar, mohon tunggu beberapa saat hingga sistem kami memverifikasinya.
                        </div>
                    @elseif($payment->status == 'success' || $payment->status == 'settlement')
                        <div class="alert alert-success" role="alert">
                            Terima kasih! Pembayaran Anda telah kami terima. Pesanan Anda akan segera kami proses.
                        </div>
                    @endif

                    <hr>

                    <h6 class="text-gradient fw-bolder mb-3">Detail Item</h6>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                @foreach($payment->orderItems as $item)
                                <tr>
                                    <td>
                                        <img src="{{ $item->file_name }}" alt="Design" class="img-fluid rounded" style="width: 70px; height: 70px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle">
                                        Custom Jersey <br>
                                        <small class="text-muted">Ukuran: {{ $item->size }}</small>
                                    </td>
                                    <td class="align-middle text-end">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="align-middle text-end fw-bold">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-top">
                                    <td colspan="3" class="text-end fw-bold">Total Pembayaran</td>
                                    <td class="text-end fw-bolder fs-5 text-gradient">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <hr>

                    <h6 class="text-gradient fw-bolder mb-3">Informasi Pengiriman</h6>
                    <p class="mb-1"><strong>Penerima:</strong> {{ $payment->name }}</p>
                    <p class="mb-1"><strong>No. HP:</strong> {{ $payment->phone }}</p>
                    <p class="mb-0"><strong>Alamat:</strong><br>{{ $payment->address }}</p>

                </div>
                <div class="card-footer bg-light text-center">
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-primary mx-1">Lanjutkan Belanja</a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="btn btn-primary mx-1">Lihat Riwayat Pesanan</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
