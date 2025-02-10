<?php


# Leer el contenido del archivo para modificarlo
file_path = "/mnt/data/monitorEntrada.php"

with open(file_path, "r", encoding="utf-8") as file:
    php_code = file.read()

# Modificar el código para incluir la nueva condición
modified_code = php_code.replace(
    '($eventName === "Usuario no registrado" && $readerName==="AndenesCalama-1-Entrada")',
    '($eventName === "Usuario no registrado" && ($readerName==="AndenesCalama-1-Entrada" || $readerName==="ParkingCalama-1-Entrada"))'
)

# Agregar la lógica específica para ParkingCalama-1-Entrada
modified_code = modified_code.replace(
    '$accessLevelId = "some_value";',  # Reemplazar un valor de accessLevelId genérico si existía
    'if ($readerName === "ParkingCalama-1-Entrada") {\n'
    '    $accessLevelId = "565656565";\n'
    '    $tarifa = "parking";\n'
    '    $tipo = "parking";\n'
    '}\n'
)

# Guardar el archivo modificado
modified_file_path = "/mnt/data/monitorEntrada_mod.php"
with open(modified_file_path, "w", encoding="utf-8") as file:
    file.write(modified_code)

# Devolver la ruta del archivo modificado
modified_file_path



?>