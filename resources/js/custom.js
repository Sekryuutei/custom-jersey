const canvas = new fabric.Canvas('jerseyCanvas');

// Load Template
fabric.loadSVGFromURL( 'product2.svg', function(objects, options) {
    const template = fabric.util.groupSVGElements(objects, options);
   
    template.scaleToWidth(300);  // Sesuaikan ukuran template
    template.scaleToHeight(200);  // Sesuaikan ukuran template
    template.selectable = false;  // Make template non-selectable
    template.left = (canvas.width - template.width * template.scaleX) / 2;
    template.top = (canvas.height - template.height * template.scaleY) / 2;
    canvas.add(template);
    canvas.add(Lato900);
    canvas.renderAll();  // Tambahkan template ke canvas
});

// Add Text
function addText() {
    const text = new fabric.Textbox('Your Text Here', {
        left: 50,
        top: 50,
        fontSize: 20,
    });
    canvas.add(text);
}

// Add Logo
function addLogo(event) {
    const reader = new FileReader();
    reader.onload = function () {
        fabric.Image.fromURL(reader.result, function (img) {
            img.scaleToWidth(100);
            img.scaleToHeight(100);
            canvas.add(img);
        });
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Save Design
function saveDesign() {
    const cdrData = canvas.toSVG(); // Export to SVG
    const formData = new FormData();
    formData.append('cdrData', cdrData);

    fetch('/save-design', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            alert('Design saved successfully!');
        })
        .catch(error => console.error('Error:', error));
}

// Delete Object
document.getElementById('deleteObject').onclick = function() {
    var activeObject = canvas.getActiveObject();
    if (activeObject) {
        canvas.remove(activeObject);
    }
};