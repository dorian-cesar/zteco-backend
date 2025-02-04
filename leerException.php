<?php
// Configuración de la API

include 'config.php';

$pageNo = 1; // Número de página
$pageSize = 20; // Cantidad de eventos por página

$fechaInicio = "2025-01-27 00:00:00"; // Cambia a la fecha deseada
$fechaFin = "2025-01-27 23:59:59"; // Cambia a la fecha deseada

// Convertir fechas a timestamp en milisegundos
$startTime = urlencode($fechaInicio);
$endTime = urlencode($fechaFin);

// Endpoint para obtener los eventos de excepción
//$apiUrl = "http://$serverIP:$serverPort/api/transaction/list?pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";
$apiUrl = "http://$serverIP:$serverPort/api/transaction/list?pageNo=$pageNo&pageSize=$pageSize&startTime=$startTime&endTime=$endTime&access_token=$apiToken";


// Inicializar cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
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
echo $response;

// Procesar la respuesta
$data = json_decode($response, true);

// if ($data && isset($data['code']) && $data['code'] === 0) {
//     echo "Eventos de excepción:\n";
//     foreach ($data['data'] as $event) {
//         echo "Fecha/Hora: " . $event['eventTime'] . "\n";
//         echo "Evento: " . $event['eventName'] . "\n";
//         echo "Área: " . $event['areaName'] . "\n";
//         echo "PIN: " . ($event['pin'] ?? "N/A") . "\n";
//         echo "Tarjeta: " . ($event['cardNo'] ?? "N/A") . "\n";
//         echo "Verificación: " . $event['verifyModeName'] . "\n";
//         echo "Dispositivo: " . $event['devName'] . "\n";
//         echo "Lector: " . $event['readerName'] . "\n";
//         echo "----------------------\n";
//     }
// } else {
//     echo "Error al obtener los eventos de excepción: " . ($data['message'] ?? "Respuesta inválida");
// }
?>
