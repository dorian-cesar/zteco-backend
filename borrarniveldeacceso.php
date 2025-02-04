<?php
// Configuración de la API
include_once 'config.php';

// Datos del usuario y nivel a eliminar
$userPin = "PTCL21"; // PIN del usuario a eliminar
$levelIds = "2c9a86e09499c21c0194b8a31c062624"; // ID del nivel a eliminar (puedes poner varios separados por comas)

// Construcción de la URL
$apiUrl = "http://$serverIP:$serverPort/api/eleLevel/deleteLevel?pin=$userPin&levelIds=$levelIds&access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
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
        "level_removed" => $levelIds
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $data['message'] ?? "Error desconocido al eliminar el nivel de acceso"
    ], JSON_PRETTY_PRINT);
}
?>
