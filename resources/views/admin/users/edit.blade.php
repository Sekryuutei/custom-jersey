@extends('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Edit User</span></h3><br>
    <div class="container" style="max-width: 600px;">
        <div class="card p-4">
            @include('admin.users._form', ['user' => $user])
        </div>
    </div>
</div>
@endsection

