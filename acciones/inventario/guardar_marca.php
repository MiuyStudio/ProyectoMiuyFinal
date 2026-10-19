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
    $nombre_marca = isset($_POST['nombre_marca']) ? trim($_POST['nombre_marca']) : '';

    // Validar que no esté vacío y que contenga letras o números válidos
    if (!empty($nombre_marca) && preg_match('/[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]/', $nombre_marca)) {
        $nombre_clean = $conn->real_escape_string($nombre_marca);

        // Verificar si la marca ya existe
        $sql_check = "SELECT id_marca FROM marcas WHERE LOWER(nombre_marca) = LOWER('$nombre_clean')";
        $res_check = $conn->query($sql_check);

        if ($res_check && $res_check->num_rows > 0) {
            header("Location: ../../modulos/inventario/agregar_marca.php?error=" . urlencode("La marca '$nombre_marca' ya existe."));
            exit();
        }

        $sql = "INSERT INTO marcas (nombre_marca) 
                VALUES ('$nombre_clean')";

        if ($conn->query($sql)) {
            header("Location: ../../modulos/inventario/inventario.php");
            exit();
        } else {
            header("Location: ../../modulos/inventario/agregar_marca.php?error=" . urlencode("Error al guardar la marca: " . $conn->error));
            exit();
        }
    } else {
        header("Location: ../../modulos/inventario/agregar_marca.php?error=" . urlencode("El nombre de la marca debe contener al menos letras o números válidos."));
        exit();
    }
} else {
    header("Location: ../../modulos/inventario/agregar_marca.php");
    exit();
}
?>
