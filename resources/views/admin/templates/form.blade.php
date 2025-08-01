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
    action="{{ isset($template) ? route('admin.templates.update', $template->id) : route('admin.templates.store') }}"
    method="POST"
    enctype="multipart/form-data">
    @csrf
    @if(isset($template))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Nama Template</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $template->name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label for="image" class="form-label">Gambar Template (SVG, PNG, JPG)</label>
        <input type="file" class="form-control" id="image" name="image" {{ isset($template) ? '' : 'required' }}>
        @if(isset($template) && $template->image_path)
            <div class="mt-2">
                <small>Gambar saat ini:</small><br>
                <img src="{{ $template->image_path }}" alt="{{ $template->name }}" style="width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;">
            </div>
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
        @endif
    </div>


    <button type="submit" class="btn btn-primary">{{ isset($template) ? 'Update' : 'Simpan' }}</button>
    <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Batal</a>
</form>

