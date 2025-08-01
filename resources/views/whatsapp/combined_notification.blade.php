Halo {{ $payment->name }},

Terima kasih atas pesanan Anda! 🙏

Pembayaran Anda untuk pesanan #{{ $payment->id }} sebesar Rp{{ number_format($payment->amount, 0, ',', '.') }} telah berhasil kami terima.

Pesanan Anda akan segera kami proses dan kirimkan ke alamat:
{{ $payment->address }}

Kami akan memberikan update selanjutnya. Terima kasih telah berbelanja di Kustom Jersey!

--- (Detail untuk Admin) ---

ID Pesanan: {{ $payment->id }}
Order ID: {{ $payment->order_id }}
Tanggal: {{ $payment->updated_at->format('d M Y, H:i') }}
Total: Rp{{ number_format($payment->amount, 0, ',', '.') }}
Metode Bayar: {{ $payment->payment_method }}

Detail Pelanggan:
Nama: {{ $payment->name }}
Email: {{ $payment->email }}
No. HP: {{ $payment->phone }}
Alamat: {{ $payment->address }}
@if($payment->orderItems)
Rincian Pesanan:
@foreach($payment->orderItems as $item)
- {{ $item->quantity }}x Jersey (Size: {{ $item->size }}) - Link: {{ $item->file_name }}
@endforeach
@endif
