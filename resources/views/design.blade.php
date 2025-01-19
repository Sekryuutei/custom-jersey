@extends('master')
@section('content')
    <div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
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
        <h3 class="display-5 fw-bolder"><span class="text-gradient d-inline">Design Your Jersey</span></h3>
        <div>
            <button class="btn btn-outline-dark btn-lg px-3 py-2 fs-6 fw-bolder" id="addText">Add Text</button>
            <button class="btn btn-outline-dark btn-lg px-3 py-2 fs-6 fw-bolder" id="uploadImageButton">Upload Image</button>
            <input type="file" id="uploadImage" accept="image/*">
            <button class="btn btn-outline-dark btn-lg px-3 py-2 fs-6 fw-bolder" id="deleteObject">Hapus Objek</button>
        </div>
        
        <div id="canvas-container">
            <canvas id="jerseyCanvas" width="500" height="600"></canvas>
        </div>
        <button class="btn btn-primary btn-lg px-5 py-3 me-sm-3 fs-6 fw-bolder" id="saveDesignButton">Checkout</button>
        <script>
        
        const canvas = new fabric.Canvas("jerseyCanvas");

        fabric.loadSVGFromURL("{{ asset('storage/' . $template->image_path) }}", function (objects, options) {
            const template = fabric.util.groupSVGElements(objects, options);

            template.scaleToWidth(500); // Sesuaikan ukuran template
            template.scaleToHeight(400); // Sesuaikan ukuran template
            template.selectable = false; // Template tidak berubah
            template.left = (canvas.width - template.width * template.scaleX) / 2;
            template.top = (canvas.height - template.height * template.scaleY) / 2;
            canvas.add(template);
            canvas.add(Lato900);
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

        document.getElementById('saveDesignButton').onclick = function() {
            const cdrData = canvas.toSVG(); // Export to SVG
            const formData = new FormData();
            formData.append("cdrData", cdrData);

            fetch("/save-design", {
                method: "POST",
                body: formData,
            })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    alert("Checkout berhasil");
                } else {
                    alert("Maaf belum bisa checkout masih dalam tahap pengembangan");
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Maaf belum bisa checkout masih dalam tahap pengembangan");
            });
        };

        </script>
    </div>
@endsection