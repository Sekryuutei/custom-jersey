@extends('master')
@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tambah Template Baru</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.store') }}" method="POST" id="template-form">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Template</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="image-file" class="form-label">File Gambar</label>
                            <input type="file" class="form-control" id="image-file" accept="image/svg+xml" required>
                            <div class="form-text">Hanya file SVG yang diizinkan. Ukuran file maksimal 4.5 MB.</div>
                        </div>
                        
                        <input type="hidden" name="image_path" id="image_path">

                        <button type="submit" class="btn btn-primary" id="submit-button">Simpan</button>
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('template-form');
    const fileInput = document.getElementById('image-file');
    const imageUrlInput = document.getElementById('image_path');
    const submitButton = document.getElementById('submit-button');

    const CLOUDINARY_URL = `https://api.cloudinary.com/v1_1/{{ $cloudinaryCloudName }}/image/upload`;
    const CLOUDINARY_UPLOAD_PRESET = '{{ $cloudinaryUploadPreset }}';

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (fileInput.files.length === 0) { return; }

        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('upload_preset', CLOUDINARY_UPLOAD_PRESET);

        submitButton.disabled = true;
        submitButton.textContent = 'Mengunggah...';

        fetch(CLOUDINARY_URL, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.secure_url) {
                    imageUrlInput.value = data.secure_url;
                    form.submit(); // Kirim form ke Laravel setelah URL didapat
                } else {
                    alert('Gagal mengunggah gambar ke Cloudinary. Silakan coba lagi.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Simpan';
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat mengunggah. Periksa konsol untuk detail.');
                submitButton.disabled = false;
                submitButton.textContent = 'Simpan';
            });
    });
});
</script>
@endpush
