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
    $id_equipo     = isset($_POST['id_equipo']) ? intval($_POST['id_equipo']) : 0;
    $accion        = isset($_POST['accion']) ? $_POST['accion'] : 'guardar';
    $nombre        = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $id_categoria  = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
    $id_marca      = isset($_POST['id_marca']) ? intval($_POST['id_marca']) : 0;
    $id_modelo     = isset($_POST['id_modelo']) ? intval($_POST['id_modelo']) : 0;
    $estado        = isset($_POST['estado']) ? trim($_POST['estado']) : '';

    if ($id_equipo <= 0) {
        header("Location: ../../modulos/inventario/inventario.php");
        exit();
    }

    if ($accion === 'dar_baja') {
        // Acción: Dar de baja el equipo
        $sql = "UPDATE equipos SET estado = 'De Baja' WHERE id_equipo = $id_equipo";

        if ($conn->query($sql)) {
            header("Location: ../../modulos/inventario/inventario.php?mensaje=" . urlencode("El equipo ha sido dado de baja correctamente."));
            exit();
        } else {
            header("Location: ../../modulos/inventario/ver_equipo.php?id=" . $id_equipo . "&error=" . urlencode("Error al dar de baja el equipo: " . $conn->error));
            exit();
        }
    } else {
        // Acción: Guardar cambios
        if (!empty($nombre) && $id_categoria > 0 && $id_marca > 0 && $id_modelo > 0 && !empty($estado)) {
            $nombre_clean = $conn->real_escape_string($nombre);
            $estado_clean = $conn->real_escape_string($estado);

            $sql = "UPDATE equipos SET 
                        nombre = '$nombre_clean',
                        id_categoria = $id_categoria,
                        id_marca = $id_marca,
                        id_modelo = $id_modelo,
                        estado = '$estado_clean'
                    WHERE id_equipo = $id_equipo";

            if ($conn->query($sql)) {
                header("Location: ../../modulos/inventario/inventario.php?mensaje=" . urlencode("Equipo actualizado correctamente."));
                exit();
            } else {
                header("Location: ../../modulos/inventario/ver_equipo.php?id=" . $id_equipo . "&error=" . urlencode("Error al actualizar el equipo: " . $conn->error));
                exit();
            }
        } else {
            header("Location: ../../modulos/inventario/ver_equipo.php?id=" . $id_equipo . "&error=" . urlencode("Por favor complete todos los campos obligatorios."));
            exit();
        }
    }
} else {
    header("Location: ../../modulos/inventario/inventario.php");
    exit();
}
?>
