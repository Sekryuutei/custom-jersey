<section>
    <h5 class="card-title">{{ __('Informasi Profil') }}</h5>
    @if($user->role !== 'admin')
        <p class="card-text text-muted small">{{ __("Perbarui informasi profil, kontak, dan alamat email akun Anda.") }}</p>
    @else
        <p class="card-text text-muted small">{{ __("Perbarui informasi profil dan alamat email akun Anda.") }}</p>
    @endif
    <hr class="my-3">

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Nama') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-muted">
                        {{ __('Alamat email Anda belum terverifikasi.') }}
                        <button form="send-verification" class="btn btn-link btn-sm p-0 m-0 align-baseline">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2 small">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if($user->role !== 'admin')
            <div class="mb-3">
                <label for="phone" class="form-label">{{ __('No HP') }}</label>
                <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">{{ __('Alamat Lengkap') }}</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="3" autocomplete="street-address">{{ old('address', $user->address) }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-primary">{{ __('Simpan') }}</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="m-0 text-success small">{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
