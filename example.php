<?php

// Incluye el autoload de Composer para cargar automáticamente las dependencias
require 'vendor/autoload.php';

// Importa las clases necesarias de la librería Firebase JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Define la clave secreta utilizada para firmar y verificar el JWT
$key = 'example_key';

// Define el payload (carga útil) del JWT, que contiene la información que se desea transmitir
$payload = [
    'iss' => 'http://example.org', // Emisor del token
    'aud' => 'http://example.com', // Audiencia del token
    'iat' => 1356999524, // Tiempo en que se emitió el token (timestamp)
    'nbf' => 1357000000 // Tiempo antes del cual el token no debe ser aceptado (timestamp)
];

/**
 * IMPORTANTE:
 * Debes especificar los algoritmos soportados por tu aplicación. Ver
 * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
 * para una lista de algoritmos compatibles con la especificación.
 */

// Codifica el payload en un JWT utilizando la clave secreta y el algoritmo HS256
$jwt = JWT::encode($payload, $key, 'HS256');

// Decodifica el JWT utilizando la misma clave y algoritmo para obtener el payload original
$decoded = JWT::decode($jwt, new Key($key, 'HS256'));

// Imprime el payload decodificado
print_r($decoded);

/*
 NOTA: Ahora el payload decodificado es un objeto en lugar de un array asociativo.
 Para obtener un array asociativo, necesitas convertirlo:
*/

// Convierte el objeto decodificado en un array asociativo
$decoded_array = (array) $decoded;

/**
 * Puedes agregar una flexibilidad de tiempo (leeway) para tener en cuenta la diferencia de tiempo
 * entre los servidores de firma y verificación. Se recomienda que esta flexibilidad
 * no sea mayor a unos pocos minutos.
 *
 * Fuente: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
 */
//

// Establece una flexibilidad de tiempo de 60 segundos
JWT::$leeway = 60;

// Decodifica el JWT nuevamente para reflejar cualquier cambio en la flexibilidad de tiempo
$decoded = JWT::decode($jwt, new Key($key, 'HS256'));

// Para obtener los valores de los encabezados decodificados, decodifica el JWT manualmente
// Un JWT está compuesto por tres partes separadas por puntos ('.'): encabezado, payload y firma
$token_parts = explode('.', $jwt); // Divide el JWT en sus tres partes
$header = json_decode(base64_decode($token_parts[0])); // Decodifica la primera parte que es el encabezado

// Imprime los encabezados decodificados
print_r($header);