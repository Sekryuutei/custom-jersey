@extends('master')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="mb-4 display-5 fw-bolder"><span class="text-gradient d-inline">Profil Saya</span></h2>

            <!-- Update Profile Information -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password -->
            <div class="card mb-4">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-4">
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.index') }}" class="btn btn-secondary">Kembali ke Admin Panel</a>
                @else
                    <a href="{{ route('home') }}" class="btn btn-secondary">Kembali ke Beranda</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
