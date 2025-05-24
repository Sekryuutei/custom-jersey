@extends('master')
@section('content')
<script src="https://code.jquery.com/jquery-3.4.1.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js">
    </script>
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{config('services.midtrans.client_key')}}"></script>

        <div class="payment-form-container col-md-3 mx-auto mt-5">
            <form id="paymentForm" method="POST" action="{{ route('payment.update',  $payment->id) }}">
                @csrf
                <div class="mb-2">
                    <label>Nama</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>No HP</label>
                    <input type="text" name="phone" id="phone" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Alamat</label>
                    <input type="text" name="address" id="address" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Jumlah</label>
                    <input type="number" name="amount" id="amount" class="form-control" value="1" min="1" required>
                </div>
                <input type="hidden" name="file_name" value="{{ session('imgur_link') }}">
                <input type="hidden" name="template_id" value="{{ session('template_id') }}">
                <input type="hidden" name="price" value="{{ session('price') }}">
                <button type="submit" class="btn btn-success" id="pay-button">Checkout</button>
            </form>
        </div>

 <script>
        $("#paymentForm").submit(function(event) {
            event.preventDefault();

            $.ajax({
                url: "{{ route('payment.update', $payment->id) }}",
                type: "POST",
                data: {
                    _method: 'POST',
                    _token: '{{ csrf_token() }}',
                    file_name: $('input[name="file_name"]').val(),
                      template_id: $('input[name="template_id"]').val(),
                     price: $('input[name="price"]').val(),
                     name: $('input#name').val(),
                     email: $('input#email').val(),
                    phone: $('input#phone').val(),
                     address: $('input#address').val(),
                        amount: $('input#amount').val(),
                    payment_result: JSON.stringify(result)
                },
                dataType: "json",
                success: function(data) {
                    console.log(data);
                    if (data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function (result) {
                                alert("payment success!"); console.log(result); console.log(result);
                            },
                            onPending: function (result) {
                                alert("wating your payment!"); console.log(result);
                            },
                            onError: function (result) {
                                alert("payment failed!"); console.log(result);
                            },
                            onClose: function () {
                                alert('You closed the popup without finishing the payment');
                            }
                        });
                    }
                },
                
                error: function(xhr, status, error) {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    console.error(xhr.responseText);
                }
            });
        });
    </script>
@endsection