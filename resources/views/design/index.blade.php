@extends('master')

@section('content')
<style>
    /* Canvas container styling */
    #canvas-container {
        position: relative;
        width: 100%;
        max-width: 600px; /* Max width for the canvas */
        margin: 0 auto;
        border: 1px solid #ddd;
        border-radius: .375rem;
        background-color: #f8f9fa;
    }

    #jersey-canvas {
        width: 100%;
        height: auto;
    }

    /* Controls panel styling */
    .controls-panel {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: .375rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .control-group {
        border-top: 1px solid #eee;
        padding-top: 1rem;
        margin-top: 1rem;
    }

    .control-group:first-child {
        border-top: none;
        padding-top: 0;
        margin-top: 0;
    }

    #upload-image-input {
        display: none;
    }
</style>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bolder"><span class="text-gradient d-inline">Desain Jersey Anda</span></h2>
    </div>

    <div class="row g-5">
        <!-- Kolom Kiri: Canvas -->
        <div class="col-lg-8">
            <div id="canvas-container">
                <canvas id="jersey-canvas"></canvas>
            </div>
        </div>

        <!-- Kolom Kanan: Panel Kontrol -->
        <div class="col-lg-4">
            <div class="controls-panel">
                <div class="control-group">
                        <!-- <h5 class="fw-bolder mb-3">Aksi</h5> -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" id="add-text-btn">Tambah Teks</button>
                        <button class="btn btn-outline-secondary" id="upload-image-btn">Unggah Gambar</button>
                        <input type="file" id="upload-image-input" accept="image/*">
                    </div>
                </div>

                <!-- Kontrol untuk objek yang dipilih -->
                <div id="editing-controls" class="d-none">
                    <div class="control-group">
                        <h5 class="fw-bolder mb-3">Edit Objek</h5>
                        <div id="text-controls" class="d-none">
                            <div class="mb-3">
                                <label for="text-color-input" class="form-label">Warna Teks</label>
                                <input type="color" id="text-color-input" class="form-control form-control-color" value="#000000">
                            </div>
                            <div class="mb-3">
                                <label for="font-size-input" class="form-label">Ukuran Font</label>
                                <input type="number" id="font-size-input" value="24" min="8" max="120" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Layer</label>
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-dark btn-sm" id="bring-forward-btn">Ke Depan</button>
                                <button class="btn btn-outline-dark btn-sm" id="send-backward-btn">Ke Belakang</button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-danger" id="delete-object-btn">Hapus Objek</button>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <!-- <h5 class="fw-bolder mb-3">Selesai</h5> -->
                    <form id="design-form" action="{{ route('cart.add') }}" method="POST" class="d-grid">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                        <input type="hidden" name="designImage" id="design-image-input">
                        <button type="submit" class="btn btn-success btn-lg fw-bolder">Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvasContainer = document.getElementById('canvas-container');
    const canvas = new fabric.Canvas('jersey-canvas');
    const templateUrl = "{{ Illuminate\Support\Str::startsWith($template->image_path, 'http') ? $template->image_path : asset('assets/' . $template->image_path) }}";
    let fabricTemplate = null; // To hold the loaded SVG template

    // Function to resize and set up the canvas
    const resizeCanvas = () => {
        const containerWidth = canvasContainer.offsetWidth;
        canvas.setDimensions({
            width: containerWidth,
            height: containerWidth * 1.2 // Maintain aspect ratio
        });

        if (fabricTemplate) {
            // Recalculate scale and re-center the background
            const scale = Math.min(canvas.width / fabricTemplate.width, canvas.height / fabricTemplate.height);
            canvas.setBackgroundImage(fabricTemplate, canvas.renderAll.bind(canvas), {
                originX: 'center',
                originY: 'center',
                top: canvas.height / 2,
                left: canvas.width / 2,
                scaleX: scale,
                scaleY: scale,
            });
        }
        canvas.renderAll();
    };

    // Load template SVG as background
    fabric.loadSVGFromURL(templateUrl, (objects, options) => {
        fabricTemplate = fabric.util.groupSVGElements(objects, options);
        fabricTemplate.set({
            selectable: false,
            evented: false,
        });
        // Initial setup
        resizeCanvas();
    });

    // --- Element Selectors ---
    const editingControls = document.getElementById('editing-controls');
    const textControls = document.getElementById('text-controls');
    const textColorInput = document.getElementById('text-color-input');
    const fontSizeInput = document.getElementById('font-size-input');
    const fileInput = document.getElementById('upload-image-input');

    // --- Event Listeners for Buttons ---
    document.getElementById('add-text-btn').onclick = () => {
        const text = new fabric.IText('Teks Anda', {
            left: 50,
            top: 100,
            fontFamily: 'Arial',
            fill: '#000000',
            fontSize: 40,
            textAlign: 'center',
            originX: 'center',
            originY: 'center'
        });
        canvas.centerObject(text);
        canvas.add(text);
        canvas.setActiveObject(text);
    };

    document.getElementById('upload-image-btn').onclick = () => fileInput.click();

    fileInput.onchange = (e) => {
        const reader = new FileReader();
        reader.onload = (event) => {
            fabric.Image.fromURL(event.target.result, (img) => {
                img.scaleToWidth(150);
                canvas.add(img);
                canvas.centerObject(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
            });
        };
        reader.readAsDataURL(e.target.files[0]);
    };

    document.getElementById('delete-object-btn').onclick = () => {
        const activeObject = canvas.getActiveObject();
        if (activeObject) {
            canvas.remove(activeObject);
        }
    };

    document.getElementById('bring-forward-btn').onclick = () => {
        const activeObject = canvas.getActiveObject();
        if (activeObject) canvas.bringForward(activeObject);
    };

    document.getElementById('send-backward-btn').onclick = () => {
        const activeObject = canvas.getActiveObject();
        if (activeObject) canvas.sendBackwards(activeObject);
    };

    // --- Canvas Event Listeners ---
    const updateControls = () => {
        const activeObject = canvas.getActiveObject();
        if (activeObject) {
            editingControls.classList.remove('d-none');
            if (activeObject.type === 'i-text') {
                textControls.classList.remove('d-none');
                textColorInput.value = activeObject.get('fill');
                fontSizeInput.value = activeObject.get('fontSize');
            } else {
                textControls.classList.add('d-none');
            }
        } else {
            editingControls.classList.add('d-none');
        }
    };

    canvas.on({
        'selection:created': updateControls,
        'selection:updated': updateControls,
        'selection:cleared': updateControls,
    });

    // --- Control Event Listeners ---
    textColorInput.addEventListener('input', (e) => {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set('fill', e.target.value);
            canvas.renderAll();
        }
    });

    fontSizeInput.addEventListener('input', (e) => {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set('fontSize', parseInt(e.target.value, 10));
            canvas.renderAll();
        }
    });

    // --- Form Submission ---
    document.getElementById('design-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Selalu cegah submit untuk menampilkan konfirmasi

        Swal.fire({
            title: 'Tambah ke Keranjang?',
            text: "Apakah Anda yakin dengan desain ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Warna hijau Bootstrap
            cancelButtonColor: '#6c757d',  // Warna abu-abu Bootstrap
            confirmButtonText: 'Ya, tambahkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Hapus seleksi objek agar tidak ada border di gambar akhir
                canvas.discardActiveObject().renderAll();
                const dataURL = canvas.toDataURL({ format: 'png', quality: 1.0 });
                document.getElementById('design-image-input').value = dataURL;
                this.submit(); // Lanjutkan submit form jika dikonfirmasi
            }
        });
    });

    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resizeCanvas, 150); // Debounce resize for performance
    });
});
</script>
@endsection