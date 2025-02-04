<?php
// Configuración de la API
include 'config.php';

// Obtener el timestamp actual en milisegundos
$timestamp = round(microtime(true) * 1000); 

// Endpoint de monitoreo de transacciones
$apiUrl = "http://$serverIP:$serverPort/api/transaction/monitor?timestamp=$timestamp&access_token=$apiToken";


while(true){
    leer($apiUrl);
   
    echo"<br>";
    sleep(4);
    
}



function leer($apiUrl){
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


// Procesar la respuesta
$data = json_decode($response, true);

if ($data && isset($data['code']) && $data['code'] === 0) {

    echo $response;

} else {
    echo "Error al obtener los eventos en tiempo real: " . ($data['message'] ?? "Respuesta inválida");
}
}
?>
