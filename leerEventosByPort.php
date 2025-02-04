<?php
// Configuración de la API


$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Reemplaza con tu token válido


$doorId = "2c9a86e09499c21c0194a7ec18c60047"; // Cambia al ID de la puerta que deseas consultar
$pageNo = 1; // Número de página
$pageSize = 10; // Cantidad de eventos por página

// Endpoint para obtener eventos relacionados con la puerta
$apiUrl = "http://$serverIP:$serverPort/api/transaction/getDoorTransactions?access_token=$apiToken";

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

if ($data && isset($data['code']) && $data['code'] === 0) {
    echo "Eventos relacionados con la puerta:\n";
    foreach ($data['data'] as $event) {
        echo "ID del Evento: " . $event['id'] . "\n";
        echo "Nombre de la Puerta: " . $event['doorName'] . "\n";
        echo "Hora del Evento: " . $event['eventTime'] . "\n";
        echo "Descripción: " . $event['eventDesc'] . "\n";
        echo "----------------------\n";
    }
} else {
    echo "Error al obtener eventos: " . ($data['message'] ?? "Respuesta inválida");
}
?>
