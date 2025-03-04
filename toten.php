<?php

date_default_timezone_set('America/Santiago');
// Configuración de la API y base de datos

$serverIP = "52.87.32.226"; // Cambiar por la IP del servidor ZKBio
$serverPort = "8098";        // Cambiar por el puerto configurado
$apiToken = "019752C5986566CF780ED580D0BBB02B0637F48E4AC5481FF3BD4278C1128274"; // Reemplaza con tu token válido
$accessLevelIds = "2c9a86e09499c21c0194b8a31c062624"; // ID del nivel de acceso
$doorId = "2c9a86e09499c21c0194b82245b7251d"; // ID de la puerta de salida

// Configuración de la base de datos
$dbHost = "ls-3c0c538286def4da7f8273aa5531e0b6eee0990c.cylsiewx0zgx.us-east-1.rds.amazonaws.com";
$dbUser = "dbmasteruser";
$dbPass = "eF5D;6VzP$^7qDryBzDd,`+w(5e4*qI+";
$dbName = "masgps";




// Crear conexión
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener la última patente con estado 'Ingresado' y tipo 'Parking'
$sql = "SELECT patente, idmov FROM movParking WHERE estado = 'Ingresado' AND tipo = 'Parking' ORDER BY idmov DESC LIMIT 1";
 $result = $conn->query($sql);
 $conn->close();


 echo json_encode($result);



