@extends('master')
@section('content')
<style>
    .card-template {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: pointer;
    }
    .card-template:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    .card-template .card-img-top {
        /* Menggunakan aspect-ratio 1/1 (persegi) untuk tampilan yang konsisten */
        aspect-ratio: 1 / 1;
        object-fit: contain; /* 'contain' memastikan seluruh gambar terlihat, 'cover' akan memotong */
        background-color: #f8f9fa;
    }
</style>

<div class="container my-5">
    <h2 class="display-5 fw-bolder text-center mb-5"><span class="text-gradient d-inline">Pilih Template Jersey</span></h2>
    {{-- Menggunakan row-cols dan gap (g-4) untuk grid yang lebih modern dan konsisten --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 g-4">
        @forelse($templates as $template)
            <div class="col">
                <div class="card h-100 card-template" onclick="chooseTemplate('{{ $template->id }}')">
                    @php
                        $imageUrl = Illuminate\Support\Str::startsWith($template->image_path, 'http')
                            ? $template->image_path
                            : asset('assets/' . $template->image_path);
                    @endphp
                    <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $template->name }}">
                    {{-- d-flex dan flex-column memastikan card-body mengisi ruang dan mt-auto mendorong tombol ke bawah --}}
                    <div class="card-body text-center d-flex flex-column p-4">
                        <div class="mb-3">
                            {{-- Tampilkan Rating --}}
                            <div class="d-flex align-items-center justify-content-center">
                                @if($template->reviews->count() > 0)
                                    <span class="text-warning me-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($template->average_rating))
                                                &#9733; {{-- Bintang penuh --}}
                                            @else
                                                &#9734; {{-- Bintang kosong --}}
                                            @endif
                                        @endfor
                                    </span>
                                    <small class="text-muted">({{ $template->reviews->count() }})</small>
                                @endif
                            </div>
                        </div>
                        <!-- <h5 class="card-title fw-bolder">{{ $template->name }}</h5> -->
                        <button class="btn btn-primary btn-sm px-4 py-2 fs-6 fw-bolder mt-auto">Pilih</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center">Belum ada template yang tersedia.</p>
            </div>
        @endforelse
    </div>
</div>
<script>
    function chooseTemplate(templateId) {
        // Menggunakan helper url() Laravel untuk URL yang lebih andal
        window.location.href = `{{ url('/design') }}/${templateId}`;
    }
</script>
@endsection