<?php
// Configuración de la API
header("Content-Type: application/json"); // Respuesta en JSON

include 'config.php';

$userPin = "BFGT22"; // PIN del usuario a eliminar (debe ser dinámico según el caso)

// Construcción de la URL con el PIN del usuario
$apiUrl = "http://$serverIP:$serverPort/api/v2/person/delete?pin=$userPin&access_token=$apiToken";




$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $apiUrl,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
