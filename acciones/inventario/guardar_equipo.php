<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] == 3) {
    header("Location: ../../modulos/inventario/inventario.php?error=" . urlencode("No tenés permisos para realizar esta acción."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $numero_serie = isset($_POST['numero_serie']) ? trim($_POST['numero_serie']) : '';
    $id_marca = isset($_POST['id_marca']) ? intval($_POST['id_marca']) : 0;
    $id_modelo = isset($_POST['id_modelo']) ? intval($_POST['id_modelo']) : 0;
    $id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    if (!empty($nombre) && !empty($id_marca) && !empty($id_modelo) && !empty($id_categoria) && !empty($estado)) {
        $nombre_clean = $conn->real_escape_string($nombre);
        $num_serie_clean = $conn->real_escape_string($numero_serie);
        $estado_clean = $conn->real_escape_string($estado);

        // Validar si el número de serie ya existe
        if (!empty($num_serie_clean)) {
            $res_check_serie = $conn->query("SELECT id_equipo FROM equipos WHERE numero_serie = '$num_serie_clean'");
            if ($res_check_serie && $res_check_serie->num_rows > 0) {
                header("Location: ../../modulos/inventario/agregar_equipo.php?error=" . urlencode("El número de serie '$numero_serie' ya se encuentra registrado en otro equipo."));
                exit();
            }
        }

        $sql = "INSERT INTO equipos (nombre, numero_serie, id_marca, id_modelo, id_categoria, estado) 
                VALUES ('$nombre_clean', '$num_serie_clean', $id_marca, $id_modelo, $id_categoria, '$estado_clean')";

        if ($conn->query($sql)) {
            header("Location: ../../modulos/inventario/inventario.php");
            exit();
        } else {
            header("Location: ../../modulos/inventario/agregar_equipo.php?error=" . urlencode("Error al guardar el equipo: " . $conn->error));
            exit();
        }
    } else {
        header("Location: ../../modulos/inventario/agregar_equipo.php?error=" . urlencode("Por favor complete todos los campos obligatorios."));
        exit();
    }
} else {
    header("Location: ../../modulos/inventario/agregar_equipo.php");
    exit();
}
?>
