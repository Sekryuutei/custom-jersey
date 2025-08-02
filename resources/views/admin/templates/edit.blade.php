@extends('master')
@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Template: {{ $template->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.update', $template->id) }}" method="POST" id="template-form">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Template</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $template->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Saat Ini</label>
                            <div>
                                <img src="{{ $template->image_path }}" alt="{{ $template->name }}" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image-file" class="form-label">Ganti Gambar (Opsional)</label>
                            <input type="file" class="form-control" id="image-file" accept="image/svg+xml, image/png, image/jpeg">
                            <div class="form-text">Pilih file baru jika Anda ingin mengganti gambar yang ada. Ukuran maksimal 4.5 MB.</div>
                        </div>
                        
                        {{-- Input ini akan diisi oleh JS jika ada file baru yang diunggah --}}
                        <input type="hidden" name="image_path" id="image_path">

                        <button type="submit" class="btn btn-primary" id="submit-button">Simpan Perubahan</button>
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
        // Jika tidak ada file baru yang dipilih, biarkan form submit secara normal.
        if (fileInput.files.length === 0) {
            return;
        }

        e.preventDefault(); // Hanya cegah submit jika ada file yang perlu diunggah.

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
                    form.submit();
                } else {
                    alert('Gagal mengunggah gambar baru. Silakan coba lagi.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Simpan Perubahan';
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat mengunggah. Periksa konsol untuk detail.');
                submitButton.disabled = false;
                submitButton.textContent = 'Simpan Perubahan';
            });
    });
});
</script>
@endpush
