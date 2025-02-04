<?php
// Configuración de la API
$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Reemplaza con tu token válido

// Definir el nivel de acceso predeterminado
$accessLevelIds = "2c9a86e09499c21c0194b8a31c062624"; // ID del nivel de acceso

// Obtener el timestamp actual en milisegundos
$timestamp = round(microtime(true) * 1000);

// Endpoint de monitoreo de transacciones
$apiUrl = "http://$serverIP:$serverPort/api/transaction/monitor?timestamp=$timestamp&access_token=$apiToken";

while (true) {
    leer($apiUrl, $serverIP, $serverPort, $apiToken, $accessLevelIds);
    echo "<br>";
    sleep(3); // Esperar 4 segundos antes de la siguiente consulta
}

/**
 * Función para leer los eventos en tiempo real y procesarlos.
 */
function leer($apiUrl, $serverIP, $serverPort, $apiToken, $accessLevelIds)
{
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
        echo "Error en la solicitud: " . curl_error($curl);
        curl_close($curl);
        exit;
    }

    // Cerrar cURL
    curl_close($curl);

    // Procesar la respuesta
    $data = json_decode($response, true);

    if ($data && isset($data['code']) && $data['code'] === 0) {
        foreach ($data['data'] as $event) {
            if ($event['eventName'] === "Usuario no registrado") {
                echo "****";
                echo
                $userPin = $event['pin']; // Se usa el número de tarjeta como PIN del usuario

                if ($userPin) {
                    // 1. Crear usuario en el sistema
                    $createUserUrl = "http://$serverIP:$serverPort/api/person/add?access_token=$apiToken";
                    $userData = [
                        "pin" => $userPin,
                        "name" => "Usuario Autogenerado",
                        "password" => "",            // Contraseña (opcional)
                        "cardNo" => "",         // Número de tarjeta asociada (opcional)
                        "deptCode" => "1",               // Código del departamento (opcional)
                        "gender" => "M",                 // Género (opcional, 'M' o 'F')
                        "idNo" => "",          // Número de identificación (RUT, DNI, etc.) (opcional)
                        "email" => "" // Email del usuario (opcional)
                    ];



                    $createUserResponse = sendPostRequest($createUserUrl, $userData);

                    if ($createUserResponse['code'] === 0) {
                        // 2. Asignar nivel de acceso al usuario recién creado
                        $assignAccessUrl = "http://$serverIP:$serverPort/api/accLevel/addLevelPerson?levelIds=$accessLevelIds&pin=$userPin&access_token=$apiToken";
                        $assignAccessResponse = sendPostRequest($assignAccessUrl, []);

                        echo json_encode([
                            "eventTime" => $event['eventTime'],
                            "eventName" => $event['eventName'],
                            "cardNo" => $event['cardNo'],
                            "user_created" => $createUserResponse['message'],
                            "access_assigned" => $assignAccessResponse['message']
                        ], JSON_PRETTY_PRINT);
                    } else {
                        echo json_encode([
                            "eventTime" => $event['eventTime'],
                            "eventName" => $event['eventName'],
                            "cardNo" => $event['cardNo'],
                            "error" => "Error al crear usuario: " . $createUserResponse['message']
                        ], JSON_PRETTY_PRINT);
                    }
                }
            }
        }
    } else {
        echo "Error al obtener los eventos en tiempo real: " . ($data['message'] ?? "Respuesta inválida");
    }
}

/**
 * Función para enviar solicitudes POST a la API
 */
function sendPostRequest($url, $postData)
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}
