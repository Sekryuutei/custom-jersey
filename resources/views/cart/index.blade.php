@extends('master')

@section('content')
<style>
    /* Responsive table for mobile view */
    @media (max-width: 767px) {
        .responsive-cart-table {
            border: none;
        }
        .responsive-cart-table thead {
            display: none;
        }
        .responsive-cart-table tr {
            display: block;
            margin-bottom: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .responsive-cart-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f1f1;
        }
        .responsive-cart-table td:last-child {
            border-bottom: 0;
        }
        .responsive-cart-table td::before {
            content: attr(data-label);
            font-weight: bold;
            text-align: left;
            margin-right: 1rem;
        }
        /* Special handling for the first cell (product) */
        .responsive-cart-table td[data-label="Produk"] {
            border-bottom: 1px solid #dee2e6;
        }
    }
</style>
<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bolder mb-0"><span class="text-gradient d-inline">Keranjang Belanja</span></h1>
    </div>
    <div class="row gx-5 justify-content-center">
        <div class="col-lg-11 col-xl-9 col-xxl-8">

            @if(empty($cart))
                <div class="card text-center p-4">
                    <p>Keranjang Anda masih kosong.</p>
                    <a href="{{ route('templates.index') }}" class="btn btn-primary">Mulai Desain Sekarang</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle responsive-cart-table">
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
                            @foreach($cart as $id => $item)
                                @php
                                    $subtotal = $item['price'] * $item['quantity'];
                                @endphp
                                <tr>
                                    <td data-label="Produk">
                                        <div class="d-flex align-items-center">
                                            {{-- Pratinjau gambar yang ditumpuk --}}
                                            <div class="position-relative me-3" style="width: 80px; height: 80px;">
                                                {{-- Gambar template dasar di belakang --}}
                                                <img src="{{ Str::startsWith($item['template_image'], 'http') ? $item['template_image'] : asset('assets/' . $item['template_image']) }}" 
                                                     alt="Template" class="position-absolute img-thumbnail" 
                                                     style="width: 100%; height: 100%; object-fit: contain; background-color: #f8f9fa;">
                                                {{-- Gambar desain kustom di depan --}}
                                                <img src="{{ $item['design_image_path'] }}" 
                                                     alt="Desain Kustom" class="position-absolute" 
                                                     style="width: 100%; height: 100%; object-fit: contain; top: 0; left: 0;">
                                            </div>
                                            <span class="fw-bold">{{ $item['name'] }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Ukuran">
                                        {{-- Atribut `form` menautkan input ini ke form di kolom 'Aksi' --}}
                                        <select name="size" class="form-select form-select-sm" style="width: 80px;" form="update-form-{{ $id }}">
                                            @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                                                <option value="{{ $size }}" {{ ($item['size'] ?? 'L') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td data-label="Jumlah">
                                        {{-- Atribut `form` menautkan input ini ke form di kolom 'Aksi' --}}
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control form-control-sm" style="width: 70px;" form="update-form-{{ $id }}">
                                    </td>
                                    <td data-label="Harga">Rp{{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td data-label="Subtotal">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                                    <td data-label="Aksi">
                                        <div class="d-flex gap-1">
                                            {{-- Form untuk memperbarui item --}}
                                            <form id="update-form-{{ $id }}" action="{{ route('cart.update', $id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-primary">Ubah</button>
                                            </form>
                                            {{-- Form untuk menghapus item --}}
                                            <form action="{{ route('cart.remove', $id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus item ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Total Belanja</h5>
                        <h3 class="fw-bolder">Rp{{ number_format($totalPrice, 0, ',', '.') }}</h3>
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('checkout.index') }}" class="btn btn-success btn-lg w-100">Lanjutkan ke Pembayaran</a>
                            <a href="{{ route('templates.index') }}" class="btn btn-outline-primary">Tambah Desain Lain</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection