<?php
// Configuración de la API
$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Token de autenticación

// Datos de la patente y la fecha de autorización
$carNumber = "ABC123"; // Número de patente a agregar
$type = 0; // 0 = Lista Blanca, 1 = Lista Negra
$fromTime = "2025-02-10"; // Fecha de inicio de autorización
$toTime = "2025-02-30"; // Fecha de finalización de autorización

// Construcción de la URL de la API
$apiUrl = "http://$serverIP:$serverPort/api/parkAuthorize/addBlackWhite?type=$type&carNumber=$carNumber&fromTime=$fromTime&toTime=$toTime&access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);

// Ejecutar la solicitud
echo
$response = curl_exec($curl);

// Manejo de errores
if (curl_errno($curl)) {
    echo json_encode(["status" => "error", "message" => curl_error($curl)]);
    curl_close($curl);
    exit;
}

// Cerrar cURL
curl_close($curl);

// Decodificar respuesta JSON

?>
