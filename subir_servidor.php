<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rutaArchivo'])) {
    
    $rutaRelativa = $_POST['rutaArchivo'];
    $archivoLocal = __DIR__ . '/' . $rutaRelativa;
    
    // 1. Sanitizar el nombre (quitar espacios, acentos y caracteres raros para evitar errores en el servidor)
    $nombreCrudo = $_POST['nombrePersonalizado'];
    $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreCrudo);
    $nombreFinal = $nombreLimpio . '.pdf';

    // Verificamos que el PDF aún exista en la carpeta temp
    if (!file_exists($archivoLocal)) {
        die("❌ Error: El archivo ya no está disponible. Convierte de nuevo.");
    }

    // =========================================================================
    // ⚙️ CONFIGURACIÓN DEL SERVIDOR FTP (Sustituir cuando tengas los datos)
    // =========================================================================
    $ftp_server = "ftp.tudominio.com";
    $ftp_user = "tu_usuario";
    $ftp_pass = "tu_contraseña";
    $ftp_carpeta_destino = "/public_html/pdfs_guardados/"; 
    // =========================================================================

    // MODO SIMULACIÓN: Como aún no tienes credenciales, el código se detendrá aquí 
    // y te devolverá un mensaje de éxito simulado. 
    // CUANDO TENGAS LOS DATOS: Borra o comenta las siguientes 3 líneas.
    sleep(1); // Simulamos que tarda 1 segundo en subir
    die("✅ (Simulación) Archivo preparado para guardarse en el servidor remoto como: " . $nombreFinal);


    /* // --- CÓDIGO REAL DE SUBIDA FTP (Descomentar cuando tengas los datos) ---
    
    // 2. Intentar conexión
    $conn_id = @ftp_connect($ftp_server);
    if (!$conn_id) {
        die("❌ Error: No se pudo contactar al servidor FTP.");
    }

    // 3. Autenticación
    $login_result = @ftp_login($conn_id, $ftp_user, $ftp_pass);
    if (!$login_result) {
        die("❌ Error: Usuario o contraseña FTP incorrectos.");
    }

    // Activar modo pasivo (Casi siempre es necesario para evitar bloqueos de firewall)
    ftp_pasv($conn_id, true);

    // 4. Subir el archivo
    $rutaRemota = $ftp_carpeta_destino . $nombreFinal;
    
    if (ftp_put($conn_id, $rutaRemota, $archivoLocal, FTP_BINARY)) {
        echo "✅ ¡Éxito! Archivo subido correctamente como: " . $nombreFinal;
    } else {
        echo "❌ Error: Hubo un problema al transferir el archivo.";
    }

    // 5. Cerrar conexión
    ftp_close($conn_id);
    */
} else {
    echo "Petición no válida.";
}
?>