@extends ('master')
@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center p-4 p-lg-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="display-5 fw-bolder"><span class="text-gradient d-inline">Pembayaran Berhasil!</span></h2>
                    <p class="lead fw-normal text-muted mb-4">Terima kasih, {{ $payment->name }}. Pesanan Anda sedang kami proses.</p>

                    <div class="card mb-4 text-start">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Detail Pesanan</h5>
                        </div>
                        <div class="row g-0">
                            <div class="col-md-5 d-flex align-items-center justify-content-center p-3">
                                @if($payment->file_name)
                                    <img src="{{ $payment->file_name }}" alt="Design" class="img-fluid rounded shadow-sm">
                                @else
                                    <div class="text-muted">No Design</div>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>ID Pesanan:</span>
                                        <strong>#{{ $payment->order_id ?? $payment->id }}</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Tanggal:</span>
                                        <strong>{{ $payment->created_at->format('d F Y') }}</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Total Pembayaran:</span>
                                        <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Status:</span>
                                        <span class="badge bg-success text-capitalize">{{ $payment->status }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-4 mt-3">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection