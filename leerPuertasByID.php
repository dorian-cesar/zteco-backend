<?php
// Configuración de la API
include'config.php';


$controllerId = "2c9a86e09499c21c0194b822458324e1"; // Cambia al ID del controlador obtenido


$apiUrl = "http://$serverIP:$serverPort/api/door/list?controllerId=$controllerId&pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";

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
echo $response
// Procesar la respuesta
// $data = json_decode($response, true);

// if ($data && isset($data['code']) && $data['code'] === 0) {
//     echo "Lista de puertas del controlador:\n";
//     foreach ($data['data'] as $door) {
//         echo "ID: " . $door['id'] . " - Nombre: " . $door['name'] . "\n";
//     }
// } else {
//     echo "Error al obtener la lista de puertas: " . ($data['message'] ?? "Respuesta inválida");
// }
?>
