<?php
// Configuración de la API
include 'config.php';


$userPin = "GKSB78";          // PIN del usuario (ID único del usuario)
$accessLevelIds = "2c9a86e094662c810194662dee8f0463"; // ID del nivel de acceso (puede ser uno o varios separados por comas)

// Construir la URL del endpoint
$apiUrl = "http://$serverIP:$serverPort/api/accLevel/addLevelPerson?levelIds=$accessLevelIds&pin=$userPin&access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ]
]);

// Ejecutar la solicitud
$response = curl_exec($curl);

// Manejar errores
if (curl_errno($curl)) {
    echo "Error en la solicitud: " . curl_error($curl);
    curl_close($curl);
    exit;
}

// Cerrar cURL
curl_close($curl);

// Procesar la respuesta
$data = json_decode($response, true);

if ($data && isset($data['code']) && $data['code'] === 0) {
    echo "Nivel(es) de acceso asignado(s) exitosamente al usuario.\n";
} else {
    echo "Error al asignar el nivel de acceso: " . ($data['message'] ?? "Respuesta inválida");
}
?>
