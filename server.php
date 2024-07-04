<?php

// Requiere el archivo autoload.php para cargar automáticamente las clases necesarias
require './vendor/autoload.php';

// Crea una instancia de Dotenv y carga el archivo .env desde la ruta especificada
$dotenv = \Dotenv\Dotenv::createImmutable('../storagedir');
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

var_dump($_SERVER["REQUEST_URI"]);

// Verifica el método de solicitud HTTP y ejecuta el código correspondiente
switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
    case 'GET':
        if (array_key_exists("id", $_GET)){
            // Escapa el valor de $_GET["id"] para prevenir inyecciones SQL
            $id = intval($_GET["id"]);
            // Consulta para obtener todos los registros de la tabla 'usuarios'
            $query = "SELECT id, email, nombre, apellido, fecha_creacion, rol, contrato_firmado, fecha_firma_contrato FROM usuarios WHERE id = " . $id;
            $result = $mysqli->query($query);
            if ($result) {
                // Obtiene los datos en un arreglo asociativo
                $data = $result->fetch_assoc();
                // Establece el tipo de contenido de la respuesta a JSON
                header('Content-Type: application/json');
                // Codifica los datos en formato JSON y los imprime
                echo json_encode($data);
            } else {
                // Si hubo un error en la consulta, envía un código de respuesta 500 y un mensaje de error en formato JSON
                http_response_code(500);
                echo json_encode(['error' => 'Error en la consulta']);
            }
        } else {
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
        }
        break;
    
        case 'POST':
            // Obtén los datos enviados en el cuerpo de la solicitud
            $input = json_decode(file_get_contents('php://input'), true);
    
            // Verifica si los datos necesarios están presentes
            if (isset($input['email'], $input['nombre'], $input['apellido'], $input['rol'], $input['password'])) {
                // Escapa los valores para prevenir inyecciones SQL
                $email = $mysqli->real_escape_string($input['email']);
                $nombre = $mysqli->real_escape_string($input['nombre']);
                $apellido = $mysqli->real_escape_string($input['apellido']);
                $rol = $mysqli->real_escape_string($input['rol']);
                $hashed_password = password_hash($input['password'], PASSWORD_BCRYPT);
                $fecha_creacion = date('Y-m-d H:i:s');
                $contrato_firmado = isset($input['contrato_firmado']) ? intval($input['contrato_firmado']) : 0;
                $fecha_firma_contrato = isset($input['fecha_firma_contrato']) ? $mysqli->real_escape_string($input['fecha_firma_contrato']) : null;
    
                // Construye la consulta de inserción
                $query = "INSERT INTO usuarios (email, nombre, apellido, fecha_creacion, rol, password, contrato_firmado, fecha_firma_contrato) VALUES ('$email', '$nombre', '$apellido', '$fecha_creacion', '$rol', '$hashed_password', $contrato_firmado, " . ($fecha_firma_contrato ? "'$fecha_firma_contrato'" : "NULL") . ")";
    
                // Ejecuta la consulta de inserción
                if ($mysqli->query($query)) {
                    // Si la inserción fue exitosa, envía un código de respuesta 201 y el ID del nuevo usuario
                    http_response_code(201);
                    echo json_encode(['id' => $mysqli->insert_id]);
                } else {
                    // Si hubo un error en la inserción, envía un código de respuesta 500 y un mensaje de error en formato JSON
                    http_response_code(500);
                    echo json_encode(['error' => 'Error en la inserción']);
                }
            } else {
                // Si faltan datos necesarios, envía un código de respuesta 400 y un mensaje de error en formato JSON
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
            }
            break;
    
    case 'PUT':
        // Obtén los datos enviados en el cuerpo de la solicitud
        $input = json_decode(file_get_contents('php://input'), true);
    
        // Verifica si los datos necesarios están presentes
        if (isset($input['id'], $input['email'], $input['nombre'], $input['apellido'], $input['rol'])) {
            // Escapa los valores para prevenir inyecciones SQL
            $id = intval($input['id']);
            $email = $mysqli->real_escape_string($input['email']);
            $nombre = $mysqli->real_escape_string($input['nombre']);
            $apellido = $mysqli->real_escape_string($input['apellido']);
            $rol = $mysqli->real_escape_string($input['rol']);
            $contrato_firmado = isset($input['contrato_firmado']) ? intval($input['contrato_firmado']) : 0;
            $fecha_firma_contrato = isset($input['fecha_firma_contrato']) ? $mysqli->real_escape_string($input['fecha_firma_contrato']) : null;
    
            // Construye la consulta de actualización
            $query = "UPDATE usuarios SET email = '$email', nombre = '$nombre', apellido = '$apellido', rol = '$rol', contrato_firmado = $contrato_firmado";
    
            // Verifica si se debe actualizar la fecha de firma del contrato
            if ($fecha_firma_contrato !== null) {
                // Formatea la fecha para asegurar que sea un formato válido para MySQL
                $fecha_firma_contrato = date('Y-m-d H:i:s', strtotime($fecha_firma_contrato));
                $query .= ", fecha_firma_contrato = '$fecha_firma_contrato'";
            } else {
                $query .= ", fecha_firma_contrato = NULL";
            }
    
            $query .= " WHERE id = $id";
    
            // Ejecuta la consulta de actualización
            if ($mysqli->query($query)) {
                // Si la actualización fue exitosa, envía un código de respuesta 200 y un mensaje de éxito
                http_response_code(200);
                echo json_encode(['success' => 'Usuario actualizado']);
            } else {
                // Si hubo un error en la actualización, envía un código de respuesta 500 y un mensaje de error en formato JSON
                http_response_code(500);
                echo json_encode(['error' => 'Error en la actualización']);
            }
        } else {
            // Si faltan datos necesarios, envía un código de respuesta 400 y un mensaje de error en formato JSON
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
        }

        break;

    case 'DELETE':
        $id = intval($_GET["id"]);
        // Consulta para obtener todos los registros de la tabla 'usuarios'
        $query = "DELETE FROM usuarios WHERE id = " . $id;

        // Ejecuta la consulta de eliminación
        if ($mysqli->query($query)) {
            // Si la eliminación fue exitosa, envía un código de respuesta 200 y un mensaje de éxito en formato JSON
            http_response_code(200);
            echo json_encode(['success' => 'Usuario eliminado']);
        } else {
            // Si hubo un error en la eliminación, envía un código de respuesta 500 y un mensaje de error en formato JSON
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el usuario']);
        }
        break;
}

// Cierra la conexión a la base de datos si ya no es necesaria
$mysqli->close();
?>
