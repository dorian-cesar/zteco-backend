<?php
// Configuración de la API
$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Token de autenticación

// PIN del usuario a eliminar
$userPin = "GKSB78"; // Reemplaza con el PIN real del usuario

// Método 1: DELETE (Recomendado)
$deleteUrl = "http://$serverIP:$serverPort/api/person/delete/$userPin?access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $deleteUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "DELETE",
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);

// Ejecutar la solicitud
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Si el DELETE falla, intenta con POST
if ($httpCode !== 200) {
    curl_close($curl);

    // Método 2: POST (Alternativo)
    $deleteUrlPost = "http://$serverIP:$serverPort/api/person/delete?pin=$userPin&access_token=$apiToken";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $deleteUrlPost,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($curl);
}

// Cerrar cURL
curl_close($curl);

// Decodificar respuesta JSON
$data = json_decode($response, true);

// Verificar si la respuesta es válida
if ($data && isset($data['code']) && $data['code'] === 0) {
    echo json_encode([
        "status" => "success",
        "message" => "Usuario eliminado correctamente",
        "pin" => $userPin
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $data['message'] ?? "Error desconocido al eliminar usuario"
    ], JSON_PRETTY_PRINT);
}
?>
