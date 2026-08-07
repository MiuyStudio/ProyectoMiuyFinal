<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

    if (empty($ci) || empty($contrasena)) {
        header("Location: index.php?error=" . urlencode("Por favor ingrese su cédula y contraseña."));
        exit();
    }

    if (preg_match('/[^0-9]/', $ci)) {
        header("Location: index.php?error=" . urlencode("Ingrese su cédula solo con números, sin puntos ni guiones."));
        exit();
    }

    $ci_clean = $conn->real_escape_string($ci);
    $contrasena_clean = $conn->real_escape_string($contrasena);
    $contrasena_encriptada = md5($contrasena_clean);

    // Consulta buscando por cédula, contraseña y que el usuario esté activo
    $sql = "SELECT u.*, r.nombre_rol 
            FROM usuarios u 
            INNER JOIN roles r ON u.id_rol = r.id_rol 
            WHERE u.ci = '$ci_clean' AND u.contrasena = '$contrasena_encriptada' AND u.activo = 1";

    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Guardar datos clave en la sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_ci'] = $usuario['ci'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['usuario_rol'] = $usuario['id_rol'];
        $_SESSION['nombre_rol'] = $usuario['nombre_rol'];

        // Redirigir al sistema principal
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: index.php?error=" . urlencode("Cédula o contraseña incorrectas."));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>