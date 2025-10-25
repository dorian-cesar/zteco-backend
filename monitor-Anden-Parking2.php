<?php

date_default_timezone_set('America/Santiago');
// Configuración de la API y base de datos

$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Reemplaza con tu token válido
$accessLevelIds = "2c9a86e09499c21c0194b8a31c062624"; // ID del nivel de acceso
$doorId = "2c9a86e09499c21c0194b82245b7251d"; // ID de la puerta de salida

// Configuración de la base de datos
//$dbHost = "ls-3c0c538286def4da7f8273aa5531e0b6eee0990c.cylsiewx0zgx.us-east-1.rds.amazonaws.com";
$dbHost = "ls-ac361eb6981fc8da3000dad63b382c39e5f1f3cd.cylsiewx0zgx.us-east-1.rds.amazonaws.com";
$dbUser = "dbmasteruser";
//$dbPass = "eF5D;6VzP$^7qDryBzDd,`+w(5e4*qI+";
$dbPass="CP7>2fobZp<7Kja!Efy3Q+~g:as2]rJD";
//$dbName = "masgps";
$dbName= "parkingAndenes";

// Obtener el timestamp actual en milisegundos
$timestamp = round(microtime(true) * 1000);

// Endpoint de monitoreo de transacciones
$apiUrl = "http://$serverIP:$serverPort/api/transaction/monitor?timestamp=$timestamp&access_token=$apiToken";

while (true) {
    leer($apiUrl, $serverIP, $serverPort, $apiToken, $accessLevelIds, $dbHost, $dbUser, $dbPass, $dbName, $doorId);
    echo "<br>";
    sleep(3); // Esperar 3 segundos antes de la siguiente consulta
}

/**
 * Función para leer los eventos en tiempo real y procesarlos.
 */
 function leer($apiUrl, $serverIP, $serverPort, $apiToken, $accessLevelIds, $dbHost, $dbUser, $dbPass, $dbName, $doorId)
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
            $userPin = $event['pin']; // Extraemos el PIN del usuario
            $patente = $event['pin'];
             $eventName = $event['eventName']; // Tipo de evento
             $readerName=$event['readerName'];

            // 🚨 **Si el evento es "Usuario no registrado" y salida , muestra un mensaje en consola**
            if ($eventName === "Usuario no registrado" && $readerName === "AndenesCalama-2-Salida" || $eventName === "Usuario no registrado" && $readerName === "ParkingCalama-2-Salida") {

                echo "⚠️ ACCESO DENEGADO: El usuario con Patente {$userPin} no tiene permiso.\n";


                if($readerName === "AndenesCalama-2-Salida"){

                    $doorId="2c9a86e09499c21c0194b82245b7251d";
                    $accessLevelIds="2c9a86e09499c21c0194b8a31c062624";
                    $tiempo=20;
                    $tipo = "Anden";
                }
                if($readerName === "ParkingCalama-2-Salida"){

                    $doorId="2c9a86e09499c21c0194e674b01916ce";
                    $accessLevelIds="2c9a86e09499c21c0194e74e1d602a7d";
                    $tiempo=1;
                    $tipo = "Parking";
                }


                if (!verificarRegistroSalida($dbHost, $dbUser, $dbPass, $dbName, $patente,$tipo)){

                    if (verificarPagoSalida($dbHost, $dbUser, $dbPass, $dbName, $patente,$tipo)) {

                        abrirPuertaSalida($serverIP, $serverPort, $apiToken, $doorId, $userPin, $accessLevelIds);
                    } else {
    
                        if (verificarTiempoEstadia($dbHost, $dbUser, $dbPass, $dbName, $patente,$tiempo)) {
    
                            abrirPuertaSalida($serverIP, $serverPort, $apiToken, $doorId, $userPin, $accessLevelIds);
                        }
                    };

                    


                }
                    echo "evento duplicado";
                
            }
           
            // 📌 **Si el evento es "Usuario no registrado" y entrada **
            if ($eventName === "Usuario no registrado"&& $readerName==="AndenesCalama-1-Entrada" || $eventName === "Usuario no registrado"&& $readerName==="ParkingCalama-1-Entrada") {
                $patente = strtoupper($event['pin']); // Se usa el PIN como patente
                $fechaEntrada = date("Y-m-d"); // Fecha actual
                $horaEntrada = date("H:i:s"); // Hora actual

                if($readerName==="AndenesCalama-1-Entrada"){

                  
                 
                    $accessLevelIds = "2c9a86e09499c21c0194b8a31c062624";
                    $tarifa = "anden";
                    $tipo = "Anden";
                    $doorId="2c9a86e09499c21c0194b82245b7251c"; 


                }
                if ($readerName === "ParkingCalama-1-Entrada") {

                    $accessLevelIds = "2c9a86e09499c21c0194e74e1d602a7d";
                    $tarifa = "parking";
                    $tipo = "Parking";
                    $doorId="2c9a86e09499c21c0194e674b01916cd";


                }

                if ($userPin) {
                    // 1. Crear usuario en el sistema
                    $createUserUrl = "http://$serverIP:$serverPort/api/person/add?access_token=$apiToken";
                    $userData = [
                        "pin" => $userPin,
                        "name" => "Usuario Autogenerado",
                        "password" => "",
                        "cardNo" => "",
                        "deptCode" => "1",
                        "gender" => "M",
                        "idNo" => "",
                        "email" => ""
                    ];

                    $createUserResponse = sendPostRequest($createUserUrl, $userData);

                    if ($createUserResponse['code'] === 0) {
                        // 2. Asignar nivel de acceso al usuario recién creado
                      //  $assignAccessUrl = "http://$serverIP:$serverPort/api/accLevel/addLevelPerson?levelIds=$accessLevelIds&pin=$userPin&access_token=$apiToken";
                      //  $assignAccessResponse = sendPostRequest($assignAccessUrl, []);
                     
                      
                        // 3. Registrar en la base de datos si no ha sido registrado en la última hora
                        if (!verificarRegistroReciente($dbHost, $dbUser, $dbPass, $dbName, $patente,$tipo)) {
                         
                            registrarEntradaParking($dbHost, $dbUser, $dbPass, $dbName, $fechaEntrada, $horaEntrada, $patente,$tarifa,$tipo);
                            $parkingStatus = "Registro guardado en la base de datos";

                            if ($readerName==="AndenesCalama-1-Entrada"){
                                abrirPuertaEntrada($serverIP, $serverPort, $apiToken, $doorId, $userPin, $accessLevelIds);
                                 $parkingStatus = "Registro guardado en la base de datos y abriendo puerta de entrda Andenes";
                            }
                           
                        } else {
                            $parkingStatus = "Registro NO guardado (ya existe en la ultima hora)";
                        }

                        echo json_encode([
                            "patente"=>$patente,
                            "eventTime" => $event['eventTime'],
                            "eventName" => $event['eventName'],
                            "user_created" => $createUserResponse['message'],
                            "access_assigned" => 'test' ,//$assignAccessResponse['message'],
                            "parking_status" => $parkingStatus
                        ], JSON_PRETTY_PRINT);
                    }
                }
            }
        }
    }
}

/**
 * Función para verificar si la patente ya fue registrada en la última hora.
 */
function verificarRegistroReciente($dbHost, $dbUser, $dbPass, $dbName, $patente,$tipo)
{
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $conn->connect_error]));
    }

    // Obtener el último registro de la patente
    $stmt = $conn->prepare("SELECT horaent FROM movParking WHERE patente = ? and tipo = ? ORDER BY idmov DESC LIMIT 1");
    $stmt->bind_param("ss", $patente,$tipo);
    $stmt->execute();
    $stmt->bind_result($horaEntrada);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if (!$horaEntrada) {
        return false; // No hay registros previos, podemos registrar
    }

    // Obtener la hora actual
    $horaActual = new DateTime();
    $horaEntrada = new DateTime($horaEntrada);
    $diferencia = $horaEntrada->diff($horaActual);

    // Si han pasado menos de 5 minutos, se considera duplicado
    if ($diferencia->i < 1) {
        echo "⚠️ Registro ignorado: La patente $patente ya se registró hace menos de 5 minutos.\n";
        return true;
    }

    return false; // Se permite registrar
}


/**
 * Función para registrar la entrada del vehículo en la base de datos
 */
function registrarEntradaParking($dbHost, $dbUser, $dbPass, $dbName, $fechaEntrada, $horaEntrada, $patente,$tarifa,$tipo)
{
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $conn->connect_error]));
    }

    $stmt = $conn->prepare("INSERT INTO movParking (fechaent, horaent, patente, estado, tarifa, tipo) VALUES (?, ?, ?, 'ingresado', ?, ?)");
    $stmt->bind_param("sssss", $fechaEntrada, $horaEntrada, $patente,$tarifa,$tipo);
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

/**
 * Función para abrir la puerta de salida si el pago es válido.
 */
function abrirPuertaSalida3($serverIP, $serverPort, $apiToken, $doorId)
{
    $interval = 1; // Tiempo de apertura en segundos
    $apiUrl = "http://$serverIP:$serverPort/api/door/remoteOpenById?doorId=$doorId&interval=$interval&access_token=$apiToken";
  //  $doorId = "2c9a86e09499c21c0194b82245b7251d";

    // Inicializar cURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($curl);

    // Manejo de errores
    if (curl_errno($curl)) {
        echo json_encode(["status" => "error", "message" => curl_error($curl)]);
        curl_close($curl);
        exit;
    }

    // Cerrar cURL
    curl_close($curl);

    // Decodificar respuesta JSON
    $data = json_decode($response, true);

    // Formatear respuesta JSON
    if ($data && isset($data['code']) && $data['code'] === 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Puerta abierta exitosamente",
            "door_id" => $doorId,
            "interval" => $interval . " segundos"
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $data['message'] ?? "Error desconocido al abrir la puerta"
        ], JSON_PRETTY_PRINT);
    }
}


// Función para verificar si la patente tiene un pago registrado en la última transacción.

function verificarPagoSalida($dbHost, $dbUser, $dbPass, $dbName, $patente,$tipo)
{
   $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
   if ($conn->connect_error) {
       die("Error de conexión a la base de datos: " . $conn->connect_error);
   }

   // Buscar el último registro de la patente y verificar si el valor pagado es mayor a 0
   $stmt = $conn->prepare("SELECT valor FROM movParking WHERE patente = ? and tipo = ? ORDER BY idmov DESC LIMIT 1");
   $stmt->bind_param("ss", $patente,$tipo);
   $stmt->execute();
   $stmt->bind_result($valor);
   $stmt->fetch();
   $stmt->close();
   $conn->close();

   if($valor>0){
    echo "valor pagado:  $valor";
    return true;
   }
}

/**
 * Función para verificar si un vehículo ha estado en el estacionamiento por más de 30 minutos.
 */
function verificarTiempoEstadia($dbHost, $dbUser, $dbPass, $dbName, $patente, $tiempo)
{
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    // Consultar la última hora de entrada del vehículo
    $stmt = $conn->prepare("SELECT horaent FROM movParking WHERE patente = ? ORDER BY idmov DESC LIMIT 1");
    $stmt->bind_param("s", $patente);
    $stmt->execute();
    $stmt->bind_result($horaEntrada);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    // Si no hay registro, asumimos que el vehículo no está en el estacionamiento
    if (!$horaEntrada) {
        return false;
    }

    // Calcular la diferencia de tiempo en minutos
    $horaActual = new DateTime(); // Hora actual
    $horaEntrada = new DateTime($horaEntrada);
    $diferencia = $horaEntrada->diff($horaActual);

    // Si han pasado más de 30 minutos, devolver true
    if($diferencia->i <= $tiempo){
        echo "Tiempo de gracia menor a $tiempo,  min:$diferencia->i ";
        actualizarEstadoSalida($dbHost, $dbUser, $dbPass, $dbName, $patente);
       return true;
    }
   // return $diferencia->i <= 30;
}

/**
 * Función para abrir la puerta de salida si el pago es válido y luego eliminar al usuario.
 */
function abrirPuertaSalida($serverIP, $serverPort, $apiToken, $doorId, $userPin, $accessLevelIds)
{
    $interval = 1; // Tiempo de apertura en segundos
    $apiUrl = "http://$serverIP:$serverPort/api/door/remoteOpenById?doorId=$doorId&interval=$interval&access_token=$apiToken";

    // Inicializar cURL para abrir la puerta
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($curl);

    // Manejo de errores
    if (curl_errno($curl)) {
        echo json_encode(["status" => "error", "message" => curl_error($curl)]);
        curl_close($curl);
        exit;
    }

    // Cerrar cURL
    curl_close($curl);

    // Decodificar respuesta JSON
    $data = json_decode($response, true);

    // Si la puerta se abrió correctamente, eliminar el usuario
    if ($data && isset($data['code']) && $data['code'] === 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Puerta abierta exitosamente",
            "door_id" => $doorId,
            "interval" => $interval . " segundos"
        ], JSON_PRETTY_PRINT);

        // 🛑 **Ahora eliminamos el usuario**
        eliminarUsuario($serverIP, $serverPort, $apiToken, $userPin, $accessLevelIds);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $data['message'] ?? "Error desconocido al abrir la puerta"
        ], JSON_PRETTY_PRINT);
    }
}


function abrirPuertaEntrada($serverIP, $serverPort, $apiToken, $doorId, $userPin, $accessLevelIds)
{
    $interval = 1; // Tiempo de apertura en segundos
    $apiUrl = "http://$serverIP:$serverPort/api/door/remoteOpenById?doorId=$doorId&interval=$interval&access_token=$apiToken";

    // Inicializar cURL para abrir la puerta
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($curl);

    // Manejo de errores
    if (curl_errno($curl)) {
        echo json_encode(["status" => "error", "message" => curl_error($curl)]);
        curl_close($curl);
        exit;
    }

    // Cerrar cURL
    curl_close($curl);

    // Decodificar respuesta JSON
    $data = json_decode($response, true);

    // Si la puerta se abrió correctamente, eliminar el usuario
    if ($data && isset($data['code']) && $data['code'] === 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Puerta de entrada abierta exitosamente",
            "door_id" => $doorId,
            "interval" => $interval . " segundos"
        ], JSON_PRETTY_PRINT);
    }
        
      
}





/**
 * Función para eliminar al usuario después de abrir la puerta.
 */
function eliminarUsuario($serverIP, $serverPort, $apiToken, $userPin, $accessLevelIds)
{
    // URL para quitar el nivel de acceso del usuario
    $apiUrl = "http://$serverIP:$serverPort/api/accLevel/deleteLevel?pin=$userPin&levelIds=$accessLevelIds&access_token=$apiToken";

    // Inicializar cURL para eliminar el nivel de acceso
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($curl);

    // Manejo de errores
    if (curl_errno($curl)) {
        echo json_encode(["status" => "error", "message" => "Error al eliminar nivel de acceso: " . curl_error($curl)]);
        curl_close($curl);
        exit;
    }

    // Cerrar cURL
    curl_close($curl);

    // Decodificar respuesta JSON
    $data = json_decode($response, true);

    if ($data && isset($data['code']) && $data['code'] === 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Nivel de acceso eliminado correctamente",
            "pin" => $userPin,
            "level_removed" => $accessLevelIds
        ], JSON_PRETTY_PRINT);

        // 🛑 **Ahora eliminamos completamente al usuario**
     //   eliminarUsuarioPorPin($serverIP, $serverPort, $apiToken, $userPin);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $data['message'] ?? "Error desconocido al quitar el nivel de acceso"
        ], JSON_PRETTY_PRINT);
    }
}

/**
 * Función para eliminar completamente al usuario de la API.
 */
function eliminarUsuarioPorPin($serverIP, $serverPort, $apiToken, $userPin)
{
    // URL para eliminar al usuario
    $apiUrl = "http://$serverIP:$serverPort/api/person/delete/$userPin?access_token=$apiToken";

    // Inicializar cURL para eliminar al usuario
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($curl);

    // Manejo de errores
    if (curl_errno($curl)) {
        echo json_encode(["status" => "error", "message" => "Error al eliminar usuario: " . curl_error($curl)]);
        curl_close($curl);
        exit;
    }

    // Cerrar cURL
    curl_close($curl);

    // Decodificar respuesta JSON
    $data = json_decode($response, true);

    if ($data && isset($data['code']) && $data['code'] === 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Usuario eliminado correctamente",
            "pin" => $userPin
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $data['message'] ?? "Error desconocido al eliminar usuario"
        ], JSON_PRETTY_PRINT);
    }
}


/**
 * Función para actualizar el estado de la última entrada de una patente a "salida sin costo".
 */
function actualizarEstadoSalida($dbHost, $dbUser, $dbPass, $dbName, $patente)
{
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $conn->connect_error]));
    }

    // Obtener la fecha y hora actual
    $fechaSal = date("Y-m-d");
    $horaSal = date("H:i:s");

    // Query para actualizar el estado, fecha y hora de salida
    $stmt = $conn->prepare("UPDATE movParking 
                            SET estado = 'salida sin costo', fechasal = ?, horasal = ? 
                            WHERE patente = ? 
                            ORDER BY idmov DESC 
                            LIMIT 1");
    $stmt->bind_param("sss", $fechaSal, $horaSal, $patente);
    $stmt->execute();

    // Verificar si la actualización fue exitosa
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Estado, fecha y hora de salida actualizados", "patente" => $patente], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["status" => "error", "message" => "No se encontró un registro para actualizar", "patente" => $patente], JSON_PRETTY_PRINT);
    }

    $stmt->close();
    $conn->close();
}


function verificarRegistroSalida($dbHost, $dbUser, $dbPass, $dbName, $patente,$tipo)
{
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn->connect_error) {
        die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos: " . $conn->connect_error]));
    }

    // Obtener el último registro de la patente
    $stmt = $conn->prepare("SELECT horasal FROM movParking WHERE patente = ? and tipo= ? ORDER BY idmov DESC LIMIT 1");
    $stmt->bind_param("ss", $patente, $tipo);
    $stmt->execute();
    $stmt->bind_result($horaEntrada);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if (!$horaEntrada) {
        return false; // No hay registros previos, podemos registrar
    }

    // Obtener la hora actual
    $horaActual = new DateTime();
    $horaEntrada = new DateTime($horaEntrada);
    $diferencia = $horaEntrada->diff($horaActual);

    // Si han pasado menos de 5 minutos, se considera duplicado
    if ($diferencia->i < 1) {
        echo "⚠️ Registro ignorado: La patente $patente ya se registró Salida hace menos de 1 minutos.\n";
        return true;
    }

    return false; // Se permite registrar
}



?>
