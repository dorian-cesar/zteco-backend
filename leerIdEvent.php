<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON


include_once("config.php");


$pageNo = 1; // Número de página
$pageSize = 20; // Cantidad de eventos por página

// Endpoint para obtener los eventos
$apiUrl = "http://$serverIP:$serverPort/api/transaction/list?pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";

// Inicializar cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);

// Ejecutar la solicitud
$response = curl_exec($curl);

// Manejo de errores
if (curl_errno($curl)) {
    echo json_encode(["error" => curl_error($curl)]);
    curl_close($curl);
    exit;
}

// Cerrar cURL
curl_close($curl);
echo $response;
// Decodificar respuesta JSON
$data = json_decode($response, true);

?>
