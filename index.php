<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Conversor Pro PDF</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="container">
        <h1>Sistema de conversión a escala de grises</h1>
        
        <form id="uploadForm" action="convertir.php" method="POST" enctype="multipart/form-data">
            <div class="file-input-container">
                <input type="file" name="documento" id="fileInput" accept=".doc,.docx,.pdf" required>
                <label for="fileInput" id="fileLabel">Seleccionar archivo (Word o PDF)</label>
            </div>

            <div class="progress-wrapper" id="progressWrapper" style="display:none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="statusText">Procesando archivo...</p>
            </div>

            <div class="buttons">
                <button type="submit" class="btn-convert">Convertir a PDF / Gris</button>
                <a id="downloadBtn" class="btn-download" style="display:none;">Descargar Resultado</a>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('uploadForm');
        const downloadBtn = document.getElementById('downloadBtn');
        const progressWrapper = document.getElementById('progressWrapper');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileLabel.innerText = "Archivo: " + this.files[0].name;
                fileLabel.style.borderColor = "#28a745"; // Opcional: poner borde verde al seleccionar
            } else {
                fileLabel.innerText = "Seleccionar archivo (Word o PDF)";
                fileLabel.style.borderColor = "#611232";
            }
        });
        
        form.onsubmit = async (e) => {
    e.preventDefault();
    
    const fill = document.getElementById('progressFill');
    const status = document.getElementById('statusText');
    const wrapper = document.getElementById('progressWrapper');
    const downloadBtn = document.getElementById('downloadBtn');

    // Inicio: 10%
    wrapper.style.display = 'block';
    downloadBtn.style.display = 'none';
    fill.style.width = '10%';
    status.innerText = "Subiendo archivo...";

    const formData = new FormData(form);
    
    // Simulamos avance mientras el servidor procesa (Etapa 2: 60%)
    setTimeout(() => { 
        if(fill.style.width === '10%') {
            fill.style.width = '60%';
            status.innerText = "Convirtiendo y aplicando escala de grises...";
        }
    }, 1500);

    try {
        const response = await fetch('convertir.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.text();

        if (result.startsWith("SUCCESS:")) {
            // Final: 100%
            fill.style.width = '100%';
            status.innerText = "¡Proceso completado con éxito!";
            
            const fileUrl = result.split(":")[1];
            downloadBtn.href = fileUrl;
            downloadBtn.download = "resultado_final.pdf";
            
            // Aparece el botón de descarga con un efecto suave
            setTimeout(() => {
                downloadBtn.style.display = 'block';
            }, 500);
            
        } else {
            fill.style.background = "#ff4444"; // Rojo si hay error
            status.innerText = "Error en el proceso.";
            alert(result);
        }
    } catch (error) {
        status.innerText = "Error de conexión.";
        console.error(error);
    }
};
    </script>
</body>
</html>