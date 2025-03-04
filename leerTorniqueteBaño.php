<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON

include 'config.php';

$userPin ="988817"; // PIN del usuario

$apiUrl = "http://$serverIP:$serverPort/api/transaction/person/$userPin?access_token=$apiToken&pageNo=1&pageSize=10";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $apiUrl,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);
curl_close($curl);

// Decodificar el JSON recibido
$data = json_decode($response, true); // true -> convierte a array

// Verificar si existe la clave 'data' y tiene elementos
if (isset($data['data']) && is_array($data['data']) && count($data['data']) > 0) {
    foreach ($data['data'] as $event) {
        // Buscar si hay un evento con "Verificación en segundo plano exitosa"
        if (isset($event['eventName']) && $event['eventName'] === "Verificación en segundo plano exitosa") {
            echo json_encode([
                "message" => "El boleto ha sido ocupado.",
                "eventTime" => $event['eventTime'],
                "doorName" => $event['doorName']
            ], JSON_PRETTY_PRINT);
            exit; // Salir del script después de encontrar el evento
        }
    }

    // Si no se encuentra el evento, significa que el boleto aún no ha sido usado
    echo json_encode(["message" => "El boleto es válido y no ha sido ocupado."], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "No data found"]);
}
?>
