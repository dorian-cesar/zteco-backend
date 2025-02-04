<?php
// URL del endpoint de la API
$apiUrl = "http://52.87.32.226:8098/api/v1/controllers"; // Ajusta según tu documentación de API

// Token de seguridad
$securityToken = "tu_token_de_seguridad";

// Configuración de la solicitud cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $securityToken", // Enviar el token en el encabezado de autorización
        "Content-Type: application/json"
    ],
]);

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($curl);

// Verificar si hubo errores
if (curl_errno($curl)) {
    echo "Error en la solicitud: " . curl_error($curl);
    curl_close($curl);
    exit;
}

// Cerrar cURL
curl_close($curl);

// Decodificar la respuesta JSON
$data = json_decode($response, true);

// Procesar la respuesta
if (isset($data['controllers'])) {
    echo "Listado de IDs de controladores:\n";
    foreach ($data['controllers'] as $controller) {
        echo "ID: " . $controller['id'] . " - Nombre: " . $controller['name'] . "\n";
    }
} else {
    echo "No se encontraron controladores o hubo un error: \n";
    echo $response;
}
?>
