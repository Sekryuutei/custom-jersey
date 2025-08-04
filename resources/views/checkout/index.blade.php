@extends('master')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{config('services.midtrans.client_key')}}"></script>

<div class="container px-5 my-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bolder mb-0"><span class="text-gradient d-inline">Checkout</span></h1>
    </div>
    <div class="row gx-5 justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4">
                <h5 class="mb-3">Ringkasan Pesanan</h5>
                <ul class="list-group mb-4">
                    @foreach($cart as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <img src="{{ $item['design_image_path'] }}" width="50" class="me-2 img-thumbnail">
                                Custom Jersey {{ $item['name'] }} ({{ $item['quantity'] }}x, Size: {{ $item['size'] }})
                            </div>
                            <span>Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                        <span>Total</span>
                        {{-- Menggunakan variabel $totalPrice yang sudah dihitung di controller --}}
                        <span>Rp{{ number_format($totalPrice, 0, ',', '.') }}</span>
                    </li>
                </ul>

                <h5 class="mb-3">Alamat Pengiriman</h5>
                <form id="checkoutForm">
                    @csrf
                    <div class="mb-2">
                        <label for="name">Nama</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
                    </div>
                    <div class="mb-2">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ auth()->user()->email ?? '' }}" required>
                    </div>
                    <div class="mb-2">
                        <label for="phone">No HP (Format: 08...)</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ auth()->user()->phone ?? '' }}" required>
                    </div>
                    <div class="mb-2">
                        <label for="address">Alamat Lengkap</label>
                        <textarea name="address" id="address" class="form-control" required>{{ auth()->user()->address ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg mt-3" id="pay-button">Bayar Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const form = this;
    const payButton = document.getElementById('pay-button');
    payButton.disabled = true;
    payButton.textContent = 'Memproses...';

    // Menggunakan Fetch API modern sebagai pengganti jQuery.ajax
    fetch("{{ route('checkout.process') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(new FormData(form)))
    })
    .then(response => {
        if (!response.ok) {
            // Jika respons tidak OK (misal: 4xx, 5xx), lempar error untuk ditangkap di blok .catch
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        if (data.snap_token) {
            Swal.fire({
                title: 'Lanjutkan Pembayaran?',
                text: "Anda akan diarahkan ke halaman pembayaran Midtrans.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    snap.pay(data.snap_token, {
                        onSuccess: function (result) {
                            window.location.href = "/order/" + data.payment_id;
                        },
                        onPending: function (result) {
                            window.location.href = "/order/" + data.payment_id;
                        },
                        onError: function (result) {
                            Swal.fire('Pembayaran Gagal', 'Terjadi kesalahan saat memproses pembayaran Anda.', 'error');
                            payButton.disabled = false;
                            payButton.textContent = 'Bayar Sekarang';
                        },
                        onClose: function () {
                            payButton.disabled = false;
                            payButton.textContent = 'Bayar Sekarang';
                        }
                    });
                } else {
                    payButton.disabled = false;
                    payButton.textContent = 'Bayar Sekarang';
                }
            });
        }
    })
    .catch(error => {
        const errorMessage = error.message || 'Terjadi kesalahan. Silakan coba lagi.';
        Swal.fire('Terjadi Kesalahan', errorMessage, 'error');
        payButton.disabled = false;
        payButton.textContent = 'Bayar Sekarang';
    });
});
</script>
@endsection
