<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON

include 'config.php';

$pageNo = 1; // Número de página
$pageSize = 20; // Cantidad de niveles por página

// Endpoint para obtener los niveles de eventos
$apiUrl = "http://$serverIP:$serverPort/api/eventLevel/list?pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);

// Ejecutar la solicitud
$response = curl_exec($curl);
echo $response;
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
    $eventLevels = [];
    foreach ($data['data'] as $level) {
        $eventLevels[] = [
            "id" => $level['id'],
            "name" => $level['name'],
            "description" => $level['description'] ?? "Sin descripción"
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Lista de niveles de eventos obtenida correctamente",
        "total_levels" => count($eventLevels),
        "event_levels" => $eventLevels
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $data['message'] ?? "Respuesta inválida"
    ], JSON_PRETTY_PRINT);
}
?>
