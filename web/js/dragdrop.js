document.addEventListener("DOMContentLoaded", function () {
    const dropZone = document.getElementById("drop-zone");
    const fileInput = document.getElementById("file-input");

    // Abrir selector al hacer clic en el área de arrastre
    dropZone.addEventListener("click", () => fileInput.click());

    // Añadir clase al arrastrar archivos sobre el área
    dropZone.addEventListener("dragover", (event) => {
        event.preventDefault();
        dropZone.classList.add("dragover");
    });

    // Eliminar clase al salir del área
    dropZone.addEventListener("dragleave", () => {
        dropZone.classList.remove("dragover");
    });

    // Procesar archivos soltados
    dropZone.addEventListener("drop", (event) => {
        event.preventDefault();
        dropZone.classList.remove("dragover");

        const files = event.dataTransfer.files;
        handleFiles(files);
    });

    // Procesar archivos seleccionados manualmente
    fileInput.addEventListener("change", (event) => {
        const files = event.target.files;
        handleFiles(files);
    });

    function handleFiles(files) {
        [...files].forEach((file) => {
            console.log(`Archivo: ${file.name}, Tamaño: ${file.size} bytes`);
        });
        alert("Se han cargado " + files.length + " archivo(s).");
    }
});
