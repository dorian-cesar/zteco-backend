<?php
// Configuración de la API y base de datos
$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Reemplaza con tu token válido
$accessLevelIds = "2c9a86e09499c21c0194b8a31c062624"; // ID del nivel de acceso

// Configuración de la base de datos
$dbHost= "ls-3c0c538286def4da7f8273aa5531e0b6eee0990c.cylsiewx0zgx.us-east-1.rds.amazonaws.com";
$dbUser = "dbmasteruser";
$dbPass = "eF5D;6VzP$^7qDryBzDd,`+w(5e4*qI+";
$dbName = "masgps";

// Obtener el timestamp actual en milisegundos
$timestamp = round(microtime(true) * 1000);

// Endpoint de monitoreo de transacciones
$apiUrl = "http://$serverIP:$serverPort/api/transaction/monitor?timestamp=$timestamp&access_token=$apiToken";

while (true) {
    leer($apiUrl, $serverIP, $serverPort, $apiToken, $accessLevelIds, $dbHost, $dbUser, $dbPass, $dbName);
    echo "<br>";
    sleep(3); // Esperar 3 segundos antes de la siguiente consulta
}

/**
 * Función para leer los eventos en tiempo real y procesarlos.
 */
function leer($apiUrl, $serverIP, $serverPort, $apiToken, $accessLevelIds, $dbHost, $dbUser, $dbPass, $dbName)
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
                $userPin = $event['pin']; // Se usa el número de tarjeta como PIN del usuario
                $patente = strtoupper($event['pin']); // Se usa la tarjeta como patente
                $fechaEntrada = date("Y-m-d"); // Fecha actual
                $horaEntrada = date("H:i:s"); // Hora actual

                if ($userPin) {
                    // 1. Crear usuario en el sistema
                    $createUserUrl = "http://$serverIP:$serverPort/api/person/add?access_token=$apiToken";
                    $userData = [
                        "pin" => $userPin,
                        "name" => "Usuario Autogenerado",
                        "password" => "",
                        "cardNo" => '',
                        "deptCode" => "1",
                        "gender" => "M",
                        "idNo" => "",
                        "email" => ""
                    ];

                    $createUserResponse = sendPostRequest($createUserUrl, $userData);

                    if ($createUserResponse['code'] === 0) {
                        // 2. Asignar nivel de acceso al usuario recién creado
                        $assignAccessUrl = "http://$serverIP:$serverPort/api/accLevel/addLevelPerson?levelIds=$accessLevelIds&pin=$userPin&access_token=$apiToken";
                        $assignAccessResponse = sendPostRequest($assignAccessUrl, []);

                        // 3. Registrar en la base de datos si no ha sido registrado en la última hora
                        if (!verificarRegistroReciente($dbHost, $dbUser, $dbPass, $dbName, $patente)) {
                            registrarEntradaParking($dbHost, $dbUser, $dbPass, $dbName, $fechaEntrada, $horaEntrada, $patente);
                            $parkingStatus = "Registro guardado en la base de datos";
                        } else {
                            $parkingStatus = "Registro NO guardado (ya existe en la última hora)";
                        }

                        echo json_encode([
                            "eventTime" => $event['eventTime'],
                            "eventName" => $event['eventName'],
                            "cardNo" => $event['cardNo'],
                            "user_created" => $createUserResponse['message'],
                            "access_assigned" => $assignAccessResponse['message'],
                            "parking_status" => $parkingStatus
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
 * Función para verificar si la patente ya fue registrada en la última hora.
 */
function verificarRegistroReciente($dbHost, $dbUser, $dbPass, $dbName, $patente)
{
    // Conectar a la base de datos
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $conn->connect_error]));
    }

    // Consultar si la patente ya existe en la última hora
    $stmt = $conn->prepare("SELECT COUNT(*) FROM movParking WHERE patente = ? AND fechaent = CURDATE() AND horaent >= NOW() - INTERVAL 1 HOUR");
    $stmt->bind_param("s", $patente);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    return $count > 0;
}

/**
 * Función para registrar la entrada del vehículo en la base de datos
 */
function registrarEntradaParking($dbHost, $dbUser, $dbPass, $dbName, $fechaEntrada, $horaEntrada, $patente)
{
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $conn->connect_error]));
    }

    $stmt = $conn->prepare("INSERT INTO movParking (fechaent, horaent, patente, estado, tarifa,tipo) VALUES (?, ?, ?, 'EN PROCESO', 'anden','Anden')");
    $stmt->bind_param("sss", $fechaEntrada, $horaEntrada, $patente);
    $stmt->execute();
    $stmt->close();
    $conn->close();
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
?>
