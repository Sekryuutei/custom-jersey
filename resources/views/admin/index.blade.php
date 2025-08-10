@extends ('master')
@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="display-6 fw-bolder mb-0"><span class="text-gradient d-inline">Dashboard Admin</span></h2>
        <div>
            <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-primary">Kelola Template</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">Kelola Pelanggan</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Pesanan Terbaru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td><strong>#{{ $payment->order_id ?? $payment->id }}</strong></td>
                                <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $payment->name }}</td>
                                <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $statusClass = 'bg-secondary';
                                        if ($payment->status == 'success' || $payment->status == 'settlement') $statusClass = 'bg-success';
                                        if ($payment->status == 'pending') $statusClass = 'bg-warning text-dark';
                                        if (in_array($payment->status, ['failed', 'expire', 'cancel'])) $statusClass = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $statusClass }} text-capitalize">{{ $payment->status }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.orders.show', $payment->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection