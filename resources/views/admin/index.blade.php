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

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="all" @selected(!isset($filters['status']) || $filters['status'] == 'all')>Semua Status</option>
                        <option value="success" @selected(isset($filters['status']) && $filters['status'] == 'success')>Success/Settlement</option>
                        <option value="pending" @selected(isset($filters['status']) && $filters['status'] == 'pending')>Pending</option>
                        <option value="failed" @selected(isset($filters['status']) && $filters['status'] == 'failed')>Failed/Expire</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari Nama/Order ID</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Ketik untuk mencari..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
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
        <div class="card-footer bg-light">
            <strong>Total Pendapatan (dari hasil filter): Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection