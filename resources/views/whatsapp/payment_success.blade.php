Pembayaran anda telah berhasil!
Terima kasih telah menggunakan jasa layanan kami
Pesanan anda sedang diproses, mohon ditunggu

No: {{ $payment->id }}
Nama: {{ $payment->name }}
Email: {{ $payment->email }}
Alamat: {{ $payment->address }}
Ukuran: {{ $payment->size }}
Jumlah: {{ $payment->quantity }}
Harga: {{ number_format($payment->amount, 0, ',', '.') }}
Link desain: {{ $payment->file_name }}