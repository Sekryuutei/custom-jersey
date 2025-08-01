        @if(isset($template) && $template->image_path)
            <div class="mt-2">
                <small>Gambar saat ini:</small><br>
                @php
                    $imageUrl = Illuminate\Support\Str::startsWith($template->image_path, 'http')
                        ? $template->image_path
                        : asset('assets/' . $template->image_path);
                @endphp
                <img src="{{ $imageUrl }}" alt="{{ $template->name }}" style="width: 150px;">
            </div>
        @endif
    </div>
