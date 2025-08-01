@extends('master')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
    <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Kelola Template Jersey</span></h3><br>
    <div class="container">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">Tambah Template Baru</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Gambar</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td>{{ $template->id }}</td>
                        <td>{{ $template->name }}</td>
                        <td>
                            <img src="{{ $template->image_path }}" alt="{{ $template->name }}" style="width: 100px; height: auto;">
                        </td>
                        <td>{{ $template->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.templates.edit', $template->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada template.</td>
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

