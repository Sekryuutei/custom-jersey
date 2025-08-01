@extends('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Kelola Akun Pelanggan</span></h3><br>
    <div class="container">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah User Baru</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            <a href="{{ route('admin.index') }}" class="btn btn-secondary">Kembali ke Halaman Admin</a>
        </div>
    </div>
</div>
@endsection

