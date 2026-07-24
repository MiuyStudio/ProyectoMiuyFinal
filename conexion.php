<?php
// CONEXIÓN A LA BASE DE DATOS USANDO .ENV Y MYSQLI

// 1. Leer el archivo .env en una sola línea usando la función nativa parse_ini_file
$env = file_exists(__DIR__ . '/.env') ? parse_ini_file(__DIR__ . '/.env') : [];

// 2. Tomar los datos del .env (si no existen, usa los valores por defecto de XAMPP)
$db_host = $env['DB_HOST']     ?? 'localhost';
$db_user = $env['DB_USER']     ?? 'root';
$db_pass = $env['DB_PASS']     ?? $env['DB_PASSWORD'] ?? '';
$db_name = $env['DB_NAME']     ?? 'bd_proyectofinal';

// 3. Crear la conexión con la base de datos usando MySQLi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// 4. Verificar si hubo algún error al conectar
if ($conn->connect_error) {
    die("Error al conectar con la base de datos: " . $conn->connect_error);
}

// 5. Soporte para caracteres especiales (acentos, eñes, etc.)
$conn->set_charset("utf8mb4");
?>