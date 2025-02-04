<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON

include 'config.php';


$pageNo = 1; // Número de página
$pageSize = 1; // Cantidad de transacciones por página (máximo 1000)

// Definir rango de fechas en formato YYYY-MM-DD HH:MM:SS
$fechaInicio = "2025-01-27 00:00:00"; // Cambia a la fecha deseada
$fechaFin = "2025-01-27 23:59:59"; // Cambia a la fecha deseada

// Convertir fechas a formato compatible con la API
$startDate = urlencode($fechaInicio);
$endDate = urlencode($fechaFin);

// Construcción de la URL con los parámetros de fecha
$apiUrl = "http://$serverIP:$serverPort/api/transaction/list?pageNo=$pageNo&pageSize=$pageSize&startDate=$startDate&endDate=$endDate&access_token=$apiToken";

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
    $transactions = [];
    foreach ($data['data'] as $transaction) {
        $transactions[] = [
            "id" => $transaction['id'],
            "eventTime" => $transaction['eventTime'],
            "eventName" => $transaction['eventName'],
            "personName" => trim($transaction['name'] . " " . $transaction['lastName']),
            "deptName" => $transaction['deptName'] ?? "Sin departamento",
            "areaName" => $transaction['areaName'] ?? "No definida",
            "cardNo" => $transaction['cardNo'] ?? "N/A",
            "deviceName" => $transaction['devName'],
            "readerName" => $transaction['readerName'],
            "verifyMode" => $transaction['verifyModeName']
        ];
    }

    echo json_encode([
        "status" => "success",
        "message" => "Lista de transacciones obtenida correctamente",
        "total_transactions" => count($transactions),
        "start_date" => $fechaInicio,
        "end_date" => $fechaFin,
        "transactions" => $transactions
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $data['message'] ?? "Respuesta inválida"
    ], JSON_PRETTY_PRINT);
}
?>
