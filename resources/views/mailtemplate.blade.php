<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Oceana Corporation</title>
</head>
<body>
    <h3>Terima kasih, pesanan Anda telah berhasil!</h3>
    <table>
      <tr>
        <td>Order Id</td>
        <td>:</td>
        <td>{{$mail['order_id'] ?? ''}}</td>
      </tr>
      <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{$mail['name'] ?? ''}}</td>
      </tr>
      <tr>
        <td>Email</td>
        <td>:</td>
        <td>{{$mail['email'] ?? ''}}</td>
      </tr>
      <tr>
        <td>No Hp</td>
        <td>:</td>
        <td>{{$mail['phone'] ?? ''}}</td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{$mail['address'] ?? ''}}</td>
      </tr>
      <tr>
        <td>Total Harga</td>
        <td>:</td>
        <td>{{$mail['amount'] ?? ''}}</td>
      </tr>
    </table>
<!-- 
    <ul>
        <li><strong>Nama:</strong> {{ $mail['name'] ?? '' }}</li>
        <li><strong>Email:</strong> {{ $mail['email'] ?? '' }}</li>
        <li><strong>Alamat:</strong> {{ $mail['address'] ?? '' }}</li>
        <li><strong>Jumlah:</strong> {{ $mail['quantity'] ?? '' }}</li>
        <li><strong>Harga:</strong> {{ $mail['price'] ?? '' }}</li>
        <li><strong>Status:</strong> {{ $mail['status'] ?? '' }}</li>
    </ul> -->

    @if(!empty($mail['file_name']))
        <img src="{{ $mail['file_name'] }}" alt="Design" style="width: 100px; height: auto;">
    @else
        <p>Tidak ada desain yang diunggah.</p>
    @endif
</body>
</html>