<?php
/**
 * Función manual para cargar variables de entorno de forma compatible
 */
function cargarEnv($ruta) {
    if (!file_exists($ruta)) {
        return;
    }
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        // Ignorar comentarios
        if (strpos(trim($linea), '#') === 0) {
            continue;
        }
        // Dividir la línea en nombre y valor
        if (strpos($linea, '=') !== false) {
            list($nombre, $valor) = explode('=', $linea, 2);
            $nombreClean = trim($nombre);
            $valorClean = trim($valor);
            
            // Guardar en múltiples arrays para asegurar compatibilidad total
            $_ENV[$nombreClean] = $valorClean;
            $_SERVER[$nombreClean] = $valorClean;
        }
    }
}

// Detectar entorno
$is_localhost = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1');

// Cargar el archivo .env desde la raíz
cargarEnv(__DIR__ . '/.env');

// Asignación de variables buscando en $_ENV, $_SERVER o usando el fallback de desarrollo
$db_host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost';
$db_user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'bd_proyectofinal';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>