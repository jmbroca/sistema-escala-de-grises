<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SCEG</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="container">
        <h1>Sistema de Conversión a Escala de Grises</h1>
        
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

            <div id="uploadSection">
                <input type="text" id="customFileName" placeholder="Nombre del archivo para el servidor">
                
                <button type="button" id="btnUploadServer" class="btn-convert">
                    Subir al Servidor
                </button>
                <p id="uploadStatusText"></p>
            </div>
        </form>
    </div>

    <script src="script.js"></script>

</body>
</html>