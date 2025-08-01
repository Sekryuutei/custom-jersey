@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
    method="POST">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" {{ isset($user) ? '' : 'required' }}>
        @if(isset($user))
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
        @endif
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
    </div>

    <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Simpan' }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
</form>

