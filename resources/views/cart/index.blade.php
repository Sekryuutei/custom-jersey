@extends('master')

@section('content')
<div class="container px-5 my-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bolder mb-0"><span class="text-gradient d-inline">Keranjang Belanja</span></h1>
    </div>
    <div class="row gx-5 justify-content-center">
        <div class="col-lg-11 col-xl-9 col-xxl-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($cartItems->isEmpty())
                <div class="card text-center p-4">
                    <p>Keranjang Anda masih kosong.</p>
                    <a href="{{ route('templates.index') }}" class="btn btn-primary">Mulai Desain Sekarang</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Ukuran</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach($cartItems as $item)
                                @php
                                    $subtotal = $item->price * $item->quantity;
                                    $total += $subtotal;
                                @endphp
                                <tr>
                                    <td>
                                        <img src="{{ $item->file_name }}" alt="Desain Jersey" style="width: 80px; height: auto;">
                                    </td>
                                    <td>
                                        {{-- The `form` attribute links this input to the form in the 'Aksi' column --}}
                                        <select name="size" class="form-select form-select-sm" style="width: 80px;" form="update-form-{{ $item->id }}">
                                            @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                                                <option value="{{ $size }}" {{ $item->size == $size ? 'selected' : '' }}>{{ $size }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        {{-- The `form` attribute links this input to the form in the 'Aksi' column --}}
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control form-control-sm" style="width: 70px;" form="update-form-{{ $item->id }}">
                                    </td>
                                    <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                                    <td>
                                        {{-- Form for updating the item --}}
                                        <form id="update-form-{{ $item->id }}" action="{{ route('cart.update', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-info">Update</button>
                                        </form>
                                        {{-- Form for removing the item --}}
                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Total Belanja</h5>
                        <h3 class="fw-bolder">Rp{{ number_format($total, 0, ',', '.') }}</h3>
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('checkout.index') }}" class="btn btn-success btn-lg" onclick="return confirm('Apakah Anda yakin ingin melanjutkan ke pembayaran?');">Lanjutkan ke Pembayaran</a>
                            <a href="{{ route('templates.index') }}" class="btn btn-outline-primary">Tambah Desain Lain</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection