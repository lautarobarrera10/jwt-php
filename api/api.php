<?php

// Requiere el archivo autoload.php para cargar automáticamente las clases necesarias
require '../vendor/autoload.php';

// Crea una instancia de Dotenv y carga el archivo .env desde la ruta especificada
$dotenv = \Dotenv\Dotenv::createImmutable('../../storagedir');
$dotenv->load();

// Obtiene las variables de entorno del archivo .env
$server = $_ENV['server'];
$database = $_ENV['database'];
$username = $_ENV['username'];
$password = $_ENV['password'];
$port = $_ENV['port'];

// Crea una conexión a la base de datos MySQL usando las variables de entorno obtenidas
$mysqli = new mysqli($server, $username, $password, $database, $port);

// Verifica si hubo un error en la conexión y, de ser así, termina el script con un mensaje de error
if ($mysqli->connect_error) {
    die('Error de Conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Verifica el método de solicitud HTTP y ejecuta el código correspondiente
switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
    case 'GET':
        // Consulta para obtener todos los registros de la tabla 'usuarios'
        $query = "SELECT id, email, nombre, apellido, fecha_creacion, rol, contrato_firmado, fecha_firma_contrato FROM usuarios";
        $result = $mysqli->query($query);

        // Si la consulta fue exitosa, convierte los resultados en un array asociativo
        if ($result) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            // Establece el tipo de contenido de la respuesta a JSON
            header('Content-Type: application/json');
            // Codifica los datos en formato JSON y los imprime
            echo json_encode($data);
        } else {
            // Si hubo un error en la consulta, envía un código de respuesta 500 y un mensaje de error en formato JSON
            http_response_code(500);
            echo json_encode(['error' => 'Error en la consulta']);
        }
        break;
    
    case 'POST':
        // Código para manejar las solicitudes POST
        break;
    
    case 'PUT':
        // Código para manejar las solicitudes PUT
        break;

    case 'DELETE':
        // Código para manejar las solicitudes DELETE
        break;
}

// Cierra la conexión a la base de datos si ya no es necesaria
$mysqli->close();
?>
