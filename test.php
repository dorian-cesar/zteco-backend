<?php

            if ($eventName === "Usuario no registrado" ) {
                if($readerName==="AndenesCalama-1-Entrada"){

                  
                 
                    $accessLevelIds = "2c9a86e09499c21c0194b8a31c062624";
                    $tarifa = "anden";
                    $tipo = "anden";


                }
                if ($readerName === "ParkingCalama-1-Entrada") {

                    $accessLevelIds = "2c9a86e09499c21c0194e74e1d602a7d ";
                    $tarifa = "parking";
                    $tipo = "parking";


                }


                $patente = strtoupper($event['pin']); // Se usa el PIN como patente
                $fechaEntrada = date("Y-m-d"); // Fecha actual
                $horaEntrada = date("H:i:s"); // Hora actual

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
                        $assignAccessUrl = "http://$serverIP:$serverPort/api/accLevel/addLevelPerson?levelIds=$accessLevelIds&pin=$userPin&access_token=$apiToken";
                        $assignAccessResponse = sendPostRequest($assignAccessUrl, []);

                        // 3. Registrar en la base de datos si no ha sido registrado en la última hora
                        if (!verificarRegistroReciente($dbHost, $dbUser, $dbPass, $dbName, $patente)) {
                            registrarEntradaParking($dbHost, $dbUser, $dbPass, $dbName, $fechaEntrada, $horaEntrada, $patente);
                            $parkingStatus = "Registro guardado en la base de datos";
                        } else {
                            $parkingStatus = "Registro NO guardado (ya existe en la ultima hora)";
                        }

                        echo json_encode([
                            "eventTime" => $event['eventTime'],
                            "eventName" => $event['eventName'],
                            "user_created" => $createUserResponse['message'],
                            "access_assigned" => $assignAccessResponse['message'],
                            "parking_status" => $parkingStatus
                        ], JSON_PRETTY_PRINT);
                    }
                }
            }

?>