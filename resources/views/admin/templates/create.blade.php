@extends('master')
@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3 class="display-6 fw-bolder mb-4"><span class="text-gradient d-inline">Tambah Template Baru</span></h3>
            @include('admin.templates.form')
        </div>
    </div>
</div>
@endsection
