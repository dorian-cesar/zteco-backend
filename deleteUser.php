<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON

include 'config.php';

$userPin = "PTCL21"; // PIN del usuario a eliminar (debe ser dinámico según el caso)

// Construcción de la URL con el PIN del usuario
$apiUrl = "http://$serverIP:$serverPort/api/person/delete/$userPin?access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "DELETE",
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
$data = json_decode($response, true);

// Formatear respuesta JSON
if ($data && isset($data['code']) && $data['code'] === 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Usuario eliminado correctamente",
        "pin" => $userPin
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $data['message'] ?? "Error desconocido al eliminar el usuario"
    ], JSON_PRETTY_PRINT);
}
?>
