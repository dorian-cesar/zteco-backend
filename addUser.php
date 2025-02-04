<?php
// Configuración de la API
include "config.php";

// Endpoint para agregar un usuario
$apiUrl = "http://$serverIP:$serverPort/api/person/add?access_token=$apiToken";

// Datos del usuario a agregar
$userData = [
    "pin" => "GKSB78",                // Identificador único del usuario (obligatorio)
    "name" => "auto-2",          // Nombre completo del usuario
    "password" => "",            // Contraseña (opcional)
    "cardNo" => "",         // Número de tarjeta asociada (opcional)
    "deptCode" => "1",               // Código del departamento (opcional)
    "gender" => "M",                 // Género (opcional, 'M' o 'F')
    "idNo" => "",          // Número de identificación (RUT, DNI, etc.) (opcional)
    "email" => "" // Email del usuario (opcional)
];

// Inicializar cURL
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($userData), // Datos del usuario en formato JSON
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

// Procesar la respuesta
$data = json_decode($response, true);

if ($data && isset($data['code']) && $data['code'] === 0) {
    echo "Usuario agregado exitosamente.\n";
} else {
    echo "Error al agregar el usuario: " . ($data['message'] ?? "Respuesta inválida");
}
?>
