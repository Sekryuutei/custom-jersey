@extends('master')

@section('content')
<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bolder mb-0"><span class="text-gradient d-inline">Checkout</span></h1>
    </div>

    <form id="checkout-form">
        @csrf
        <div class="row gx-5">
            <!-- Kolom Kiri: Form Data Pelanggan & Pengiriman -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h5 class="mb-0">Data Pelanggan</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name ?? '' }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="{{ auth()->user()->phone ?? '' }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required>{{ auth()->user()->address ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Pilih Opsi Pengiriman</h5></div>
                    <div class="card-body">
                        <div id="shipping-options-loader" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Memuat...</span>
                            </div>
                            <p class="mt-2">Memuat opsi pengiriman...</p>
                        </div>
                        <div id="shipping-options-container" class="d-none">
                            <!-- Opsi pengiriman akan dimasukkan di sini oleh JavaScript -->
                        </div>
                        <input type="hidden" name="shipping_service" id="shipping_service">
                        <input type="hidden" name="shipping_cost" id="shipping_cost">
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Ringkasan Pesanan -->
            <div class="col-lg-5">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header"><h5 class="mb-0">Ringkasan Pesanan</h5></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($cart as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="my-0">{{ \Illuminate\Support\Str::limit($item['name'], 25) }} ({{$item['size']}})</h6>
                                    <small class="text-muted">Jumlah: {{ $item['quantity'] }}</small>
                                </div>
                                <span>Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                            </li>
                            @endforeach
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Subtotal Produk</span>
                                <strong>Rp<span id="subtotal-display">{{ number_format($totalPrice, 0, ',', '.') }}</span></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Ongkos Kirim</span>
                                <strong>Rp<span id="shipping-cost-display">0</span></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light">
                                <h5 class="mb-0">Total Bayar</h5>
                                <h5 class="mb-0"><strong>Rp<span id="grand-total-display">{{ number_format($totalPrice, 0, ',', '.') }}</span></strong></h5>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button type="submit" id="pay-button" class="btn btn-success btn-lg w-100">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const subtotal = parseFloat('{{ $totalPrice }}');
    let selectedShippingCost = 0;

    const shippingOptionsContainer = document.getElementById('shipping-options-container');
    const shippingOptionsLoader = document.getElementById('shipping-options-loader');
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const grandTotalDisplay = document.getElementById('grand-total-display');
    const checkoutForm = document.getElementById('checkout-form');
    const payButton = document.getElementById('pay-button');

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function updateTotals() {
        const grandTotal = subtotal + selectedShippingCost;
        shippingCostDisplay.textContent = formatRupiah(selectedShippingCost);
        grandTotalDisplay.textContent = formatRupiah(grandTotal);
    }

    // **PERBAIKAN**: Gunakan event delegation pada container untuk memastikan event listener berfungsi
    // bahkan untuk elemen yang ditambahkan secara dinamis.
    shippingOptionsContainer.addEventListener('change', function(event) {
        if (event.target && event.target.matches('.shipping-option')) {
            const radio = event.target;
            selectedShippingCost = parseFloat(radio.dataset.cost);
            document.getElementById('shipping_service').value = radio.dataset.serviceName;
            document.getElementById('shipping_cost').value = radio.dataset.cost;
            updateTotals();
        }
    });

    fetch('{{ route("shipping.options") }}')
        .then(response => response.json())
        .then(data => {
            shippingOptionsLoader.classList.add('d-none');
            shippingOptionsContainer.classList.remove('d-none');
            
            let html = '';
            data.forEach(courier => {
                html += `<h6 class="mt-3">${courier.name}</h6>`;
                courier.services.forEach(service => {
                    const uniqueId = `${courier.code}-${service.service.replace(/\s+/g, '')}`;
                    html += `
                        <div class="form-check">
                            <input class="form-check-input shipping-option" type="radio" name="shipping_option" id="${uniqueId}"
                                   data-cost="${service.cost}" data-service-name="${courier.name} - ${service.service}">
                            <label class="form-check-label w-100" for="${uniqueId}">
                                <div class="d-flex justify-content-between">
                                    <span>${service.service} <small class="text-muted">(${service.etd})</small></span>
                                    <strong>Rp${formatRupiah(service.cost)}</strong>
                                </div>
                            </label>
                        </div>
                    `;
                });
            });
            shippingOptionsContainer.innerHTML = html;
        })
        .catch(error => {
            shippingOptionsLoader.innerHTML = '<p class="text-danger">Gagal memuat opsi pengiriman. Silakan muat ulang halaman.</p>';
            console.error('Error fetching shipping options:', error);
        });

    checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        payButton.disabled = true;
        payButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';

        if (selectedShippingCost === 0 && document.querySelectorAll('.shipping-option').length > 0) {
             Swal.fire('Peringatan', 'Silakan pilih salah satu opsi pengiriman terlebih dahulu.', 'warning');
             payButton.disabled = false;
             payButton.textContent = 'Bayar Sekarang';
             return;
        }

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch('{{ route("checkout.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.snap_token) {
                snap.pay(result.snap_token, {
                    onSuccess: function(res) {
                        window.location.href = `/order/${result.payment_id}`;
                    },
                    onPending: function(res) {
                        window.location.href = `/order/${result.payment_id}`;
                    },
                    onError: function(res) {
                        Swal.fire('Pembayaran Gagal', 'Terjadi kesalahan saat memproses pembayaran.', 'error');
                    },
                    onClose: function() {
                        Swal.fire('Info', 'Anda menutup jendela pembayaran sebelum selesai.', 'info');
                    }
                });
            } else {
                Swal.fire('Error', result.message || 'Gagal mendapatkan token pembayaran.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Terjadi kesalahan. Silakan coba lagi.', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            payButton.disabled = false;
            payButton.textContent = 'Bayar Sekarang';
        });
    });
});
</script>
@endpush
