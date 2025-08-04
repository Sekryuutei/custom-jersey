@extends('master')
@section('content')
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="display-6 fw-bolder mb-0"><span class="text-gradient d-inline">Kelola Template</span></h2>
        <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">Tambah Template Baru</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Tanggal Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>{{ $template->id }}</td>
                                <td>
                                    @php
                                        $imageUrl = Illuminate\Support\Str::startsWith($template->image_path, 'http')
                                            ? $template->image_path
                                            : asset('assets/' . $template->image_path);
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $template->name }}" style="width: 80px; height: 80px; object-fit: contain; background-color: #f8f9fa;">
                                </td>
                                <td>{{ $template->name }}</td>
                                <td>{{ $template->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?');">
                                        <a href="{{ route('admin.templates.edit', $template->id) }}" class="btn btn-warning btn-sm">Ubah</a>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Belum ada template.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($templates->hasPages())
        <div class="card-footer">
            {{ $templates->links() }}
        </div>
        @endif
    </div>

        <div class="mt-4">
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
