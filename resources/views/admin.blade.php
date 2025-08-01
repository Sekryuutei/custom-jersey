@extends ('master')
@section('content')
    <div class="container-fluid px-4 my-5">
        <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Admin Page</span></h3><br>

        <div class="mb-4 d-flex justify-content-center gap-2">
            <a href="{{ route('admin.templates.index') }}" class="btn btn-info">Kelola Template</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-info">Kelola Pelanggan</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $payment->name }}</td>
                            <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status == 'success' ? 'success' : 'warning' }} text-capitalize">{{ $payment->status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $payment->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection