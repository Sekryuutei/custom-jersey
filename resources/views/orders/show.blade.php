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
                        @if($payment->shipping_status == 'processing')
                            <div class="alert alert-success" role="alert">
                                Terima kasih! Pembayaran Anda telah kami terima. Pesanan Anda sedang kami proses.
                            </div>
                        @elseif($payment->shipping_status == 'shipped')
                            <div class="alert alert-info" role="alert">
                                Pesanan Anda telah dikirim dan sedang dalam perjalanan!
                                @if($payment->tracking_number)
                                    Anda dapat melacaknya dengan nomor resi: <strong>{{ $payment->tracking_number }}</strong>.
                                @endif
                            </div>
                        @elseif($payment->shipping_status == 'delivered')
                            <div class="alert alert-success" role="alert">
                                Pesanan Anda telah tiba di tujuan. Jangan lupa untuk memberikan ulasan!
                            </div>
                        @endif
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

                    <hr>

                    <h6 class="text-gradient fw-bolder mb-3">Status Pengiriman</h6>
                    @php
                        $shippingStatusText = match($payment->shipping_status) {
                            'processing' => 'Sedang Diproses',
                            'shipped' => 'Telah Dikirim',
                            'delivered' => 'Telah Diterima',
                            'cancelled' => 'Dibatalkan',
                            default => 'Menunggu Konfirmasi'
                        };
                    @endphp
                    <p><strong>Status:</strong> {{ $shippingStatusText }}</p>
                    @if($payment->tracking_number)
                        <p><strong>No. Resi:</strong> {{ $payment->tracking_number }}</p>
                    @endif

                </div>
                <div class="card-footer bg-light text-center">
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-primary mx-1">Lanjutkan Belanja</a>
                    @auth
                        <a href="{{ route('orders.index') }}" class="btn btn-primary mx-1">Lihat Riwayat Pesanan</a>
                    @endauth
                </div>
            </div>

            {{-- Tombol Aksi Tambahan --}}
            @if($payment->shipping_status === 'delivered' && $payment->delivered_at && $payment->delivered_at->diffInDays(now()) <= 3)
                <div class="card shadow-sm mt-4">
                    <div class="card-body text-center">
                        @if(is_null($payment->return_status))
                            <p class="mb-2">Mengalami masalah dengan pesanan Anda?</p>
                            <button class="btn btn-outline-danger" onclick="document.getElementById('return-form-container').classList.toggle('d-none')">
                                Ajukan Pengembalian
                            </button>
                            <div id="return-form-container" class="d-none mt-3 text-start">
                                <form action="{{ route('orders.request_return', $payment) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="return_reason" class="form-label">Mohon jelaskan alasan pengembalian:</label>
                                        <textarea name="return_reason" id="return_reason" class="form-control" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">Kirim Pengajuan</button>
                                </form>
                            </div>
                        @else
                            <p class="mb-1">Status Pengembalian: <strong>{{ ucfirst($payment->return_status) }}</strong></p>
                            <small class="text-muted">Alasan: {{ $payment->return_reason }}</small>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bagian Konfirmasi Pesanan oleh Pelanggan --}}
    @if($payment->shipping_status === 'shipped' && auth()->check() && auth()->id() === $payment->user_id)
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="card shadow-sm text-center">
                <div class="card-body p-4">
                    <h5 class="card-title">Apakah Pesanan Anda Sudah Tiba?</h5>
                    <p class="card-text">Klik tombol di bawah ini untuk mengonfirmasi bahwa Anda telah menerima pesanan Anda.</p>
                    <form action="{{ route('orders.confirm_delivery', $payment) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg">
                            Konfirmasi Pesanan Diterima
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bagian Form Ulasan --}}
    @if($payment->shipping_status === 'delivered' && auth()->check() && auth()->id() === $payment->user_id)
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-gradient text-white">
                    <h4 class="mb-0"><span class="text-gradient d-inline">Beri Ulasan</span></h4>
                </div>
                <div class="card-body p-4">

                    {{-- Loop melalui setiap item unik dalam pesanan --}}
                    @foreach($payment->orderItems->unique('template_id') as $item)
                        @if($item->template && !in_array($item->template_id, $reviewedTemplateIds)) {{-- Pastikan template ada & belum diulas --}}
                        <form action="{{ route('reviews.store') }}" method="POST" class="mb-4 border-bottom pb-3">
                            @csrf
                            <input type="hidden" name="template_id" value="{{ $item->template->id }}">
                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">

                            <h6>Ulasan untuk: <strong>{{ $item->template->name }}</strong></h6>
                            <div class="mb-3">
                                <label class="form-label">Rating Anda</label>
                                <div class="rating">
                                    {{-- Simple star rating --}}
                                    @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="star{{$i}}-{{$item->id}}" name="rating" value="{{$i}}" required /><label for="star{{$i}}-{{$item->id}}">☆</label>
                                    @endfor
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="comment-{{$item->id}}" class="form-label">Komentar Anda (Opsional)</label>
                                <textarea name="comment" id="comment-{{$item->id}}" class="form-control" rows="3" placeholder="Bagaimana kualitas produknya?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-primary">Kirim Ulasan</button>
                        </form>
                        @elseif($item->template && in_array($item->template_id, $reviewedTemplateIds))
                        <div class="mb-4 border-bottom pb-3">
                             <h6>Ulasan untuk: <strong>{{ $item->template->name }}</strong></h6>
                             <p class="text-muted"><i class="bi bi-check-circle-fill text-success"></i> Anda sudah memberikan ulasan untuk produk ini.</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
.rating > input{ display:none; }
.rating > label { position: relative; width: 1.1em; font-size: 2rem; color: #FFD700; cursor: pointer; }
.rating > label::before{ content: "\2606"; position: absolute; opacity: 1; }
.rating > label:hover:before, .rating > label:hover ~ label:before { content: "\2605"; opacity: 1; }
.rating > input:checked ~ label:before{ content: "\2605"; }
</style>
@endpush
