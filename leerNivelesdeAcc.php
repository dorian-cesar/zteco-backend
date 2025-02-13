<?php
// Configuración de la API
include_once 'config.php';


$pageNo = 1; // Número de página
$pageSize = 20; // Cantidad de niveles por página

// Endpoint para obtener los niveles de acceso
$apiUrl = "http://$serverIP:$serverPort/api/accLevel/list?pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";

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
echo 
$response = curl_exec($curl);

// Manejar errores
if (curl_errno($curl)) {
    echo "Error en la solicitud: " . curl_error($curl);
    curl_close($curl);
    exit;
}

// Cerrar cURL
curl_close($curl);



// // Procesar la respuesta
// $data = json_decode($response, true);

// if ($data && isset($data['code']) && $data['code'] === 0) {
//     echo "Lista de niveles de acceso:\n";
//     foreach ($data['data'] as $accessLevel) {
//         echo "ID: " . $accessLevel['id'] . " - Nombre: " . $accessLevel['name'] . "\n";
//     }
// } else {
//     echo "Error al obtener los niveles de acceso: " . ($data['message'] ?? "Respuesta inválida");
// }
 ?>
