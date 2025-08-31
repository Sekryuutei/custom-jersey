@extends('master')
@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="display-6 fw-bolder mb-0"><span class="text-gradient d-inline">Detail Pesanan</span></h2>
        <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Detail & Update -->
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Item Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Desain</th>
                                    <th>Ukuran</th>
                                    <th>Jumlah</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->orderItems as $item)
                                <tr>
                                    <td>
                                        <a href="{{ $item->file_name }}" target="_blank" title="Lihat Desain">
                                            <img src="{{ $item->file_name }}" alt="Design" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        </a>
                                    </td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Info & Status -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pesanan</h5>
                </div>
                <div class="card-body">
                    <strong>Order ID:</strong><p class="mb-2">#{{ $payment->order_id }}</p>
                    <strong>Pelanggan:</strong><p class="mb-2">{{ $payment->name }} ({{ $payment->email }})</p>
                    <strong>Alamat:</strong><p class="mb-2">{{ $payment->address }}</p>
                    <hr>
                    <strong>Subtotal Produk:</strong><p class="mb-1">Rp {{ number_format($payment->amount - $payment->shipping_cost, 0, ',', '.') }}</p>
                    <strong>Ongkos Kirim:</strong><p class="mb-1">Rp {{ number_format($payment->shipping_cost, 0, ',', '.') }} ({{ $payment->shipping_service }})</p>
                    <h5 class="mt-2"><strong>Total Bayar: Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></h5>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.updateShipping', $payment->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="shipping_status" class="form-label">Status Pengiriman</label>
                            <select name="shipping_status" id="shipping_status" class="form-select" required>
                                <option value="processing" @selected($payment->shipping_status == 'processing')>Sedang Diproses</option>
                                <option value="shipped" @selected($payment->shipping_status == 'shipped')>Telah Dikirim</option>
                                <option value="delivered" @selected($payment->shipping_status == 'delivered')>Telah Diterima</option>
                                <option value="cancelled" @selected($payment->shipping_status == 'cancelled')>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tracking_number" class="form-label">Nomor Resi (jika sudah dikirim)</label>
                            <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="{{ old('tracking_number', $payment->tracking_number) }}" placeholder="Contoh: JNE123456789">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
