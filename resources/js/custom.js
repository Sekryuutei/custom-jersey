const canvas = new fabric.Canvas('jerseyCanvas');

// Load Template
fabric.loadSVGFromURL('product2.svg', function(objects, options) {
    const template = fabric.util.groupSVGElements(objects, options);
    console.log('SVG Loaded:', template);  // Log to check if SVG is loaded

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
window.addText = addText;  // Attach to window object

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
window.addLogo = addLogo;  // Attach to window object

// Save Design
function saveDesign() {
    const cdrData = canvas.toSVG(); // Export to SVG
    const formData = new FormData();
    formData.append('cdrData', cdrData);

    fetch('/save-design', {
        method: 'POST',
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
                alert('Design saved successfully!');
            } else {
                alert('Failed to save design. Please try again.');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to save design. Please try again.');
        });
}
window.saveDesign = saveDesign;  // Attach to window object

// Delete Object
document.getElementById('deleteObject').onclick = function() {
    var activeObject = canvas.getActiveObject();
    if (activeObject) {
        canvas.remove(activeObject);
    }
};