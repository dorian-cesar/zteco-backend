<?php
// Configura el tipo de contenido
header("Content-Type: application/json; charset=UTF-8");

// Permitir solicitudes desde cualquier origen (para pruebas)
header("Access-Control-Allow-Origin: *");

// Permitir métodos específicos
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

// Permitir encabezados específicos
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar el preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Configuración de la API
$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Reemplaza con tu token válido

// URL del endpoint
$pageNo = 1;
$pageSize = 10;

//$apiUrl = "http://$serverIP:$serverPort/api/device/accList?access_token=$apiToken";

$apiUrl = "http://$serverIP:$serverPort/api/device/accList?pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";

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

// Manejo de errores
if (curl_errno($curl)) {
    echo "Error en la solicitud: " . curl_error($curl);
    curl_close($curl);
    exit;
}

// Cerrar cURL
curl_close($curl);

// Procesar la respuesta
echo $response;
$data = json_decode($response, true);


if ($data && isset($data['code']) && $data['code'] === 0) {
   // echo "Lista de dispositivos:\n";
    foreach ($data['data'] as $device) {
       // echo "ID: " . $device['id'] . " - Nombre: " . $device['name'] . "\n";
       
    }
} else {
    echo "Error al obtener la lista de dispositivos: " . ($data['message'] ?? "Respuesta inválida");
}
?>
