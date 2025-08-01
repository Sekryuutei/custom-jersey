@extends('master')
@section('content')
    <div class="container my-5">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.2.4/fabric.min.js"></script>
        <style>
            #canvas-container {
                border: 1px solid #ccc;
                margin: 20px;
                width: 500px; 
                height: 600px;
            }
            #uploadImage {
                display: none;
            }
        </style>
        <div class="text-center">
            <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Desain Jersey</span></h3><br>
            <div class="mb-3">
                <button class="btn btn-outline-dark btn-lg px-3 py-2 fs-6 fw-bolder" id="addText">Tambah Teks</button>
                <button class="btn btn-outline-dark btn-lg px-3 py-2 fs-6 fw-bolder" id="uploadImageButton">Unggah Gambar</button>
                <input type="file" id="uploadImage" accept="image/*">
                <button class="btn btn-outline-dark btn-lg px-3 py-2 fs-6 fw-bolder" id="deleteObject">Hapus Objek</button>
            </div>
            <!-- Fitur tambahan untuk kustomisasi -->
            <div class="mb-3 d-flex justify-content-center align-items-center gap-3" id="editingControls" style="display: none;">
                <div>
                    <label for="textColor" class="form-label-sm">Warna Teks:</label>
                    <input type="color" id="textColor" class="form-control-color" value="#000000">
                </div>
                <div>
                    <label for="fontSize" class="form-label-sm">Ukuran Font:</label>
                    <input type="number" id="fontSize" value="24" min="8" max="120" class="form-control-sm" style="width: 70px;">
                </div>
                <button class="btn btn-outline-secondary btn-sm" id="bringForward">Bawa ke Depan</button>
                <button class="btn btn-outline-secondary btn-sm" id="sendBackward">Kirim ke Belakang</button>
            </div>
            
            <div id="canvas-container" class="mx-auto">
                <canvas id="jerseyCanvas" width="500" height="600"></canvas>
            </div>
            <form id="designForm" action="{{ route('cart.add') }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="designImage" id="designImageInput">
                <button type="submit" class="btn btn-success btn-lg px-5 py-3 me-sm-3 fs-6 fw-bolder" id="buyButton">Tambah</button>
            </form>
        </div>

<script>

        const canvas = new fabric.Canvas("jerseyCanvas");

        const templateUrl = "{{ Illuminate\Support\Str::startsWith($template->image_path, 'http') ? $template->image_path : asset('assets/' . $template->image_path) }}";


        fabric.loadSVGFromURL(templateUrl, function (objects, options) {
            const template = fabric.util.groupSVGElements(objects, options);

            template.scaleToWidth(500); // Sesuaikan ukuran template
            template.scaleToHeight(400); // Sesuaikan ukuran template
            template.selectable = false; // Template tidak berubah
            template.left = (canvas.width - template.width * template.scaleX) / 2;
            template.top = (canvas.height - template.height * template.scaleY) / 2;
            canvas.add(template);
            canvas.renderAll(); // Tambahkan template ke canvas
        });

        document.getElementById('uploadImageButton').onclick = function() {
            document.getElementById('uploadImage').click();  // Trigger file input click
        };

        document.getElementById('uploadImage').onchange = function(e) {
            var reader = new FileReader();
            reader.onload = function(event) {
                var imgObj = new Image();
                imgObj.src = event.target.result;
                imgObj.onload = function() {
                    var image = new fabric.Image(imgObj);
                    image.scaleToWidth(300);  // Adjust image size
                    image.scaleToHeight(200);  // Adjust image size
                    canvas.add(image);
                    canvas.renderAll();
                }
            }
            reader.readAsDataURL(e.target.files[0]);
        };

        document.getElementById('deleteObject').onclick = function() {
            var activeObject = canvas.getActiveObject();
            if (activeObject) {
                canvas.remove(activeObject);
            }
        };

        document.getElementById('addText').onclick = function() {
            var text = new fabric.IText('Teks Baru', {
                left: 50,
                top: 100,
                fontFamily: 'Arial',
                fill: '#000000',
                fontSize: 24
            });
            canvas.add(text);
            canvas.setActiveObject(text);  // Set teks sebagai objek aktif untuk mempermudah pengeditan
        };

        // --- Fitur Tambahan ---

        const editingControls = document.getElementById('editingControls');
        const textColorInput = document.getElementById('textColor');
        const fontSizeInput = document.getElementById('fontSize');

        function updateControls() {
            const activeObject = canvas.getActiveObject();
            if (activeObject) {
                editingControls.style.display = 'flex'; // Tampilkan kontrol
                if (activeObject.type === 'i-text') {
                    textColorInput.value = activeObject.get('fill').substring(0, 7); // Handle format warna
                    fontSizeInput.value = activeObject.get('fontSize');
                    textColorInput.disabled = false;
                    fontSizeInput.disabled = false;
                } else {
                    // Nonaktifkan kontrol spesifik teks jika objek bukan teks
                    textColorInput.disabled = true;
                    fontSizeInput.disabled = true;
                }
            } else {
                editingControls.style.display = 'none'; // Sembunyikan kontrol
            }
        }

        canvas.on({
            'selection:created': updateControls,
            'selection:updated': updateControls,
            'selection:cleared': updateControls
        });

        // Event listener untuk warna teks
        textColorInput.addEventListener('input', function() {
            const activeObject = canvas.getActiveObject();
            if (activeObject && activeObject.type === 'i-text') {
                activeObject.set('fill', this.value);
                canvas.renderAll();
            }
        });

        // Event listener untuk ukuran font
        fontSizeInput.addEventListener('input', function() {
            const activeObject = canvas.getActiveObject();
            if (activeObject && activeObject.type === 'i-text') {
                activeObject.set('fontSize', parseInt(this.value, 10));
                canvas.renderAll();
            }
        });

        // Event listener untuk layering
        document.getElementById('bringForward').onclick = function() {
            const activeObject = canvas.getActiveObject();
            if (activeObject) canvas.bringForward(activeObject);
        };

        document.getElementById('sendBackward').onclick = function() {
            const activeObject = canvas.getActiveObject();
            if (activeObject) canvas.sendBackwards(activeObject);
        };
        // Menangani event submit pada form
        document.getElementById('designForm').addEventListener('submit', function(event) {
            // Mencegah form dikirim secara langsung
            event.preventDefault();

            const dataURL = canvas.toDataURL({
                format: 'png',
                quality: 1
            });

            document.getElementById('designImageInput').value = dataURL;
            this.submit(); // Kirim form setelah data gambar diisi
        });
        </script>
    </div>
@endsection