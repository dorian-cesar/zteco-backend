<?php
// Configuración de la API

include'config.php';


$pageNo = 1; // Número de página
$pageSize = 20; // Cantidad de usuarios por página

// Endpoint para obtener la lista de usuarios
$apiUrl = "http://$serverIP:$serverPort/api/person/list?pageNo=$pageNo&pageSize=$pageSize&access_token=$apiToken";

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
    echo "Lista de usuarios registrados:\n";
    foreach ($data['data'] as $user) {
        echo "PIN: " . $user['pin'] . "\n";
        echo "Nombre: " . $user['name'] . "\n";
        echo "Departamento: " . ($user['deptName'] ?? "Sin departamento") . "\n";
        echo "----------------------\n";
    }
} else {
    echo "Error al obtener la lista de usuarios: " . ($data['message'] ?? "Respuesta inválida");
}
?>
