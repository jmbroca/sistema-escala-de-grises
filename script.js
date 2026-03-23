// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');
    const downloadBtn = document.getElementById('downloadBtn');
    const fill = document.getElementById('progressFill');
    const status = document.getElementById('statusText');
    const wrapper = document.getElementById('progressWrapper');

    // 1. Manejo del nombre del archivo al seleccionar
    fileInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            fileLabel.innerText = "Archivo: " + this.files[0].name;
            fileLabel.style.borderColor = "#28a745";
        } else {
            fileLabel.innerText = "Seleccionar archivo (Word o PDF)";
            fileLabel.style.borderColor = "#611232";
        }
    });

    // 2. Lógica de envío y barra de progreso
    form.onsubmit = async (e) => {
        e.preventDefault();
        
        wrapper.style.display = 'block';
        downloadBtn.style.display = 'none';
        fill.style.width = '10%';
        fill.style.background = '#611232'; // Reset color por si hubo error previo
        status.innerText = "Subiendo archivo...";

        const formData = new FormData(form);
        
        // Simulación de avance
        setTimeout(() => { 
            if(fill.style.width === '10%') {
                fill.style.width = '60%';
                status.innerText = "Procesando: Conversión y filtros...";
            }
        }, 1500);

        try {
            const response = await fetch('convertir.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.text();

            if (result.startsWith("SUCCESS:")) {
                fill.style.width = '100%';
                status.innerText = "¡Proceso completado con éxito!";
                
                const fileUrl = result.split(":")[1];
                downloadBtn.href = fileUrl;
                downloadBtn.download = "resultado_final.pdf";
                
                setTimeout(() => {
                    downloadBtn.style.display = 'block';
                }, 500);
                
            } else {
                fill.style.width = '100%';
                fill.style.background = "#ff4444"; 
                status.innerText = "Error en el servidor.";
                alert(result);
            }
        } catch (error) {
            status.innerText = "Error de conexión.";
            console.error(error);
        }
    };
});