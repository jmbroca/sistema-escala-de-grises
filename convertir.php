<?php
require_once __DIR__ . '/vendor/autoload.php';
use setasign\Fpdi\Fpdi;

// Función para limpiar archivos viejos (más de 1 hora)
function limpiarArchivosViejos($carpeta) {
    $horas = 1;
    $segundos = $horas * 3600;
    $archivos = glob($carpeta . "*");
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && (time() - filemtime($archivo) > $segundos)) {
            unlink($archivo);
        }
    }
}
// Ejecutar limpieza al iniciar el script
limpiarArchivosViejos(__DIR__ . '/temp/');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['documento'])) {
    $archivo = $_FILES['documento'];
    $nombreOriginal = $archivo['name'];
    $rutaTemporal = $archivo['tmp_name'];
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    
    $carpetaDestino = __DIR__ . '/temp/';
    if (!file_exists($carpetaDestino)) mkdir($carpetaDestino, 0777, true);

    $nombreBase = time();
    $rutaSubido = $carpetaDestino . $nombreBase . '.' . $extension;
    move_uploaded_file($rutaTemporal, $rutaSubido);

    $pdfFinal = $carpetaDestino . $nombreBase . '_final.pdf';
    $pdfIntermedio = $carpetaDestino . $nombreBase . '.pdf';

    // PASO 1: Convertir Word a PDF
    if ($extension == 'doc' || $extension == 'docx') {
        // Ruta completa recomendada para XAMPP en Windows
        $libreOfficePath = '"C:\Program Files\LibreOffice\program\soffice.exe"';
        
        // Ejecutamos la conversión
        $comandoWord = "$libreOfficePath --headless --convert-to pdf --outdir \"$carpetaDestino\" \"$rutaSubido\"";
        shell_exec($comandoWord);

        // Esperamos 2 segundos a que el sistema de archivos se actualice
        sleep(2); 

        // El PDF generado por LibreOffice tendrá el mismo nombre que el Word pero con .pdf
        $pdfIntermedio = $carpetaDestino . $nombreBase . '.pdf';

        // Verificación de seguridad
        if (!file_exists($pdfIntermedio) || filesize($pdfIntermedio) < 100) {
            echo "ERROR: La conversión de Word falló o el archivo está vacío.";
            exit;
        }
    } else {
        $pdfIntermedio = $rutaSubido; // Ya era PDF
    }

    // PASO 2: Ghostscript (Escala de grises + Eliminar Metadatos)
    // El parámetro -dProcessColorModel=/DeviceGray hace la magia del color
    $comandoGS = "gswin64c -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sColorConversionStrategy=Gray -dProcessColorModel=/DeviceGray -sOutputFile=\"$pdfFinal\" \"$pdfIntermedio\"";
    shell_exec($comandoGS);

    // PASO 3: ELIMINACIÓN RADICAL DE METADATOS CON EXIFTOOL (VERSIÓN AGRESIVA)
    if (file_exists($pdfFinal)) {
        $rutaExifTool = __DIR__ . '/herramientas/exiftool.exe';
        
        // Vaciamos explícitamente las etiquetas nativas del PDF que suelen sobrevivir
        $comandoExif = "\"$rutaExifTool\" -Title= -Author= -Subject= -Creator= -Producer= -Keywords= -CreateDate= -ModifyDate= -overwrite_original \"$pdfFinal\"";
        
        shell_exec($comandoExif);
    }

    // PASO 4: INSERTAR EL CÓDIGO QR
    if (file_exists($pdfFinal)) {
        // 4.1 Generar la imagen del QR (Ahora redirecciona a una web)
        
        // Aquí pones la URL a la que quieres que vayan. 
        // Puedes agregarle variables al final como el nombre del archivo original
        //$miPaginaWeb = "http://www.carmen.gob.mx/transparencia/web/index.aspx" . urlencode($nombreOriginal);
        $miPaginaWeb = "http://www.carmen.gob.mx/transparencia/web/index.aspx";
        
        // Codificamos la URL completa para que la API del QR la entienda
        $contenidoQR = urlencode($miPaginaWeb);
        $urlAPI = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $contenidoQR;
        $rutaQR = $carpetaDestino . 'qr_' . $nombreBase . '.png';
        
        // Descargamos y guardamos el QR
        file_put_contents($rutaQR, file_get_contents($urlAPI));

        // 4.2 Iniciar FPDI para manipular el PDF
        $pdf = new Fpdi();
        
        // Cargamos el PDF que ya está en grises y sin metadatos
        $pageCount = $pdf->setSourceFile($pdfFinal);

        // Recorremos todas las páginas para reconstruir el archivo
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            // Obtenemos el tamaño real de la página (A4, Carta, etc.)
            $size = $pdf->getTemplateSize($templateId);
            
            // Creamos una nueva página con el mismo tamaño y orientación
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // 4.3 Estampar el QR SOLO en la ultima página
            if ($pageNo == $pageCount) {
                // Configuramos el tamaño del QR en milímetros
                $anchoQR = 25; 
                $altoQR = 25;  
                $margen = 5; // A 15mm de los bordes
                
                // Calculamos las coordenadas para la esquina inferior derecha
                $posX = $size['width'] - $anchoQR - $margen;
                $posY = $size['height'] - $altoQR - $margen;
                
                // Pegamos la imagen
                $pdf->Image($rutaQR, $posX, $posY, $anchoQR, $altoQR);
            }
        }

        // 4.4 Sobrescribimos el archivo final con la nueva versión que trae el QR
        $pdf->Output($pdfFinal, 'F');
    }

    // PASO 5: Limpieza de residuos inmediatos
    // Borramos el archivo original (docx o pdf inicial)
    if (file_exists($rutaSubido)) {
        unlink($rutaSubido);
    }

    // Borramos el PDF intermedio (el que se creó antes del QR)
    if (file_exists($pdfIntermedio) && $pdfIntermedio != $rutaSubido) {
        unlink($pdfIntermedio);
    }

    // Borramos la imagen del QR temporal
    if (isset($rutaQR) && file_exists($rutaQR)) {
        unlink($rutaQR);
    }

    // PASO 4: Respuesta para el usuario
    if (file_exists($pdfFinal)) {
        // Enviamos una respuesta simple que el JavaScript del index leerá
        echo "SUCCESS:" . 'temp/' . basename($pdfFinal);
    } else {
        echo "ERROR: No se pudo procesar el archivo. Verifica que Ghostscript y LibreOffice estén en el PATH.";
    }
    exit;
}