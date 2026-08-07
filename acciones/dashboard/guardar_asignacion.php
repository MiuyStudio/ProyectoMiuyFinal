<?php
session_start();
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/index.php");
    exit();
}

$rol = intval($_SESSION['usuario_rol'] ?? 0);
if ($rol == 3) {
    header("Location: ../../modulos/dashboard/dashboard.php?error=" . urlencode("No tenés permisos para realizar esta acción."));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion       = trim($_POST['accion'] ?? '');
    $id_equipo    = intval($_POST['id_equipo'] ?? 0);
    $id_usuario   = intval($_POST['id_usuario'] ?? 0);
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? date('Y-m-d H:i:s'));
    // datetime-local envía el valor con 'T' como separador (2026-08-04T22:15), convertir a formato MySQL
    $fecha_inicio = str_replace('T', ' ', $fecha_inicio);
    // Si solo tiene fecha sin hora, agregar hora actual
    if (strlen($fecha_inicio) == 10) {
        $fecha_inicio .= ' ' . date('H:i:s');
    } elseif (strlen($fecha_inicio) == 16) {
        $fecha_inicio .= ':00';
    }

    if ($accion === 'asignar') {
        if ($id_equipo <= 0 || $id_usuario <= 0 || empty($fecha_inicio)) {
            header("Location: ../../modulos/dashboard/dashboard.php?error=" . urlencode("Debe seleccionar un equipo, un usuario y una fecha de inicio."));
            exit();
        }

        // Verificar que el usuario seleccionado esté activo
        $res_check_user = $conn->query("SELECT activo FROM usuarios WHERE id_usuario = $id_usuario AND activo = 1");
        if (!$res_check_user || $res_check_user->num_rows === 0) {
            header("Location: ../../modulos/dashboard/dashboard.php?error=" . urlencode("El usuario seleccionado no está activo en el sistema."));
            exit();
        }

        // Verificar que el equipo seleccionado esté Disponible
        $res_check_eq = $conn->query("SELECT estado FROM equipos WHERE id_equipo = $id_equipo AND estado = 'Disponible'");
        if (!$res_check_eq || $res_check_eq->num_rows === 0) {
            header("Location: ../../modulos/dashboard/dashboard.php?error=" . urlencode("El equipo seleccionado no está disponible para asignación (puede estar En Mantenimiento, En Uso o De Baja)."));
            exit();
        }

        $fecha_clean = $conn->real_escape_string($fecha_inicio);

        // Si el equipo ya tenía una asignación activa sin fecha_fin, la finalizamos en este momento exacto
        $sql_close_prev = "UPDATE asignaciones SET fecha_fin = NOW() WHERE id_equipo = $id_equipo AND (fecha_fin IS NULL OR fecha_fin = '0000-00-00')";
        $conn->query($sql_close_prev);

        // Registrar la nueva asignación
        $sql_insert = "INSERT INTO asignaciones (id_equipo, id_usuario, fecha_inicio) VALUES ($id_equipo, $id_usuario, '$fecha_clean')";
        if ($conn->query($sql_insert)) {
            // Actualizar estado del equipo a 'En Uso'
            $conn->query("UPDATE equipos SET estado = 'En Uso' WHERE id_equipo = $id_equipo");
            header("Location: ../../modulos/dashboard/dashboard.php?mensaje=" . urlencode("Asignación registrada correctamente."));
            exit();
        } else {
            header("Location: ../../modulos/dashboard/dashboard.php?error=" . urlencode("Error al registrar la asignación: " . $conn->error));
            exit();
        }

    } elseif ($accion === 'finalizar') {
        $id_asignacion = intval($_POST['id_asignacion'] ?? 0);

        $where = "";
        if ($id_asignacion > 0) {
            $where = "id_asignacion = $id_asignacion";
        } elseif ($id_equipo > 0) {
            $where = "id_equipo = $id_equipo AND (fecha_fin IS NULL OR fecha_fin = '0000-00-00')";
        }

        if (!empty($where)) {
            // Usar NOW() para registrar el instante exacto de finalización
            $sql_update = "UPDATE asignaciones SET fecha_fin = NOW() WHERE $where";
            if ($conn->query($sql_update)) {
                if ($id_equipo > 0) {
                    $conn->query("UPDATE equipos SET estado = 'Disponible' WHERE id_equipo = $id_equipo");
                }
                header("Location: ../../modulos/dashboard/dashboard.php?mensaje=" . urlencode("Asignación finalizada correctamente."));
                exit();
            }
        }
        header("Location: ../../modulos/dashboard/dashboard.php?error=" . urlencode("No se pudo finalizar la asignación."));
        exit();

    } else {
        header("Location: ../../modulos/dashboard/dashboard.php");
        exit();
    }
} else {
    header("Location: ../../modulos/dashboard/dashboard.php");
    exit();
}
?>
