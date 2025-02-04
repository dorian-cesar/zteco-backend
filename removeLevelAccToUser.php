<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON

include 'config.php';
$userPin = "PTCL21"; // PIN del usuario
$accessLevelIds = "2c9a86e09499c21c0194b8a31c062624"; // ID del nivel de acceso a quitar




// Construcción de la URL con los parámetros necesarios
$apiUrl = "http://$serverIP:$serverPort/api/accLevel/deleteLevel?pin=$userPin&levelIds=$accessLevelIds&access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST", // La API requiere método POST
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);

// Ejecutar la solicitud
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
$data = json_decode($response, true);

// Formatear respuesta JSON
if ($data && isset($data['code']) && $data['code'] === 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Nivel de acceso eliminado correctamente",
        "pin" => $userPin,
        "level_removed" => $accessLevelIds
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $data['message'] ?? "Error desconocido al quitar el nivel de acceso"
    ], JSON_PRETTY_PRINT);
}
?>
